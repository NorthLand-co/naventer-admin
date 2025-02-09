<?php

namespace App\Http\Controllers\Ecommerce\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ecommerce\Cart\UpdateUserCartRequest;
use App\Http\Resources\Ecommerce\Cart\UserCartResource;
use App\Models\ProductVariant;
use App\Models\UserCart as Cart;
use App\Services\Api\UserCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserCartController extends Controller
{
    protected $userCartService;

    public function __construct(UserCartService $userCartService)
    {
        $this->userCartService = $userCartService;
    }

    /**
     * Add a product to the cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {

        // Get or generate a unique token for the cart
        $temporaryToken = Session::get('temporary_token') ?? Str::uuid()->toString();
        Session::put('temporary_token', $temporaryToken);

        $userId = Auth::check() ? Auth::id() : null;
        $productVariant = ProductVariant::findByUuid($request->input('product_variant_id'));

        if (! $productVariant) {
            abort(Response::HTTP_BAD_REQUEST, 'Invalid product variant');
        }

        $quantity = $request->input('quantity') ?? 1;

        // Find the existing cart item
        $cartItem = $this->userCartService->getUserCartProduct($productVariant->id);

        if ($cartItem) {
            // Update the existing quantity
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            // Create a new cart item
            $cartItem = Cart::create([
                'session_id' => $temporaryToken,
                'product_variant_id' => $productVariant->id,
                'user_id' => $userId,
                'quantity' => $quantity,
            ]);
        }

        return (new UserCartResource($cartItem))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param  \App\Http\Requests\UpdateCartRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(UpdateUserCartRequest $request, $id)
    {
        $quantity = $request->input('quantity');

        $cartItem = $this->userCartService->getUserCartById($id);

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return (new UserCartResource($cartItem))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Get the current user's cart items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCart(Request $request)
    {
        $cartItems = $this->userCartService->GetUserCartList();
        $cartItems = $cartItems->load(['productVariant.product', 'productVariant.price']);

        if ($request->has('remove_out_of_stocks') && $request->get('remove_out_of_stocks')) {
            // Remove out-of-stock items
            foreach ($cartItems as $key => $item) {
                if (! $item->productVariant->product->is_in_stock) {
                    $this->userCartService->removeFromCartById($item->id);
                    unset($cartItems[$key]);
                }
            }
        }

        return UserCartResource::collection($cartItems);
    }

    /**
     * Remove a product from the cart.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart(Request $request, $id)
    {
        $cartItems = $this->userCartService->removeFromCartById($id);

        return response()->json(['message' => 'Product removed from cart successfully'], Response::HTTP_NO_CONTENT);
    }

    public function mergeSessionCartWithUserCart($sessionId, $userId)
    {
        // Fetch cart items associated with the session ID
        $sessionCartItems = Cart::where('session_id', $sessionId)->get();

        foreach ($sessionCartItems as $sessionCartItem) {
            // Check if the user already has the same product in their cart
            $existingCartItem = Cart::where('user_id', $userId)
                ->where('product_variant_id', $sessionCartItem->product_variant_id)
                ->first();

            if ($existingCartItem) {
                // If so, update the quantity
                $existingCartItem->quantity += $sessionCartItem->quantity;
                $existingCartItem->save();
                // Delete the session-based cart item
                $sessionCartItem->delete();
            } else {
                // If not, just update the session cart item to be associated with the user
                $sessionCartItem->user_id = $userId;
                $sessionCartItem->save();
            }
        }
    }
}

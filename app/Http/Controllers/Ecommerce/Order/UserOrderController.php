<?php

namespace App\Http\Controllers\Ecommerce\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecommerce\Order\StoreUserOrderRequest;
use App\Http\Requests\Ecommerce\Order\UpdateUserOrderRequest;
use App\Http\Resources\Ecommerce\Order\UserOrderResource;
use App\Models\OrderItem;
use App\Models\UserOrder;
use App\Services\Api\UserCartService;
use App\Services\ProductPriceService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserOrderController extends Controller
{
    protected $cartService;

    protected $productPriceService;

    public function __construct(UserCartService $cartService, ProductPriceService $productPriceService)
    {
        $this->cartService = $cartService;
        $this->productPriceService = $productPriceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->load(['orders', 'orders.items']);
        } else {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return UserOrderResource::collection($user->orders)
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    private function checkStock($cartItems)
    {
        foreach ($cartItems as $item) {
            $response = $this->validateStock($item);
            if ($response !== true) {
                return $response;
            }
        }

        return true;
    }

    private function validateStock($item)
    {
        if ($this->isStockInsufficient($item)) {
            return $this->stockErrorResponse('insufficient_stock', $item);
        }

        if ($item->productVariant->has_max_cart) {
            if ($item->quantity < ($item->product->min_cart ?? 0)) {
                return $this->stockErrorResponse('below_minimum_cart', $item);
            }
            if ($item->quantity > ($item->product->max_cart ?? 999)) {
                return $this->stockErrorResponse('exceeded_maximum_cart', $item);
            }
        }

        return true;
    }

    private function stockErrorResponse($message, $item)
    {
        return response()->json([
            'message' => $message,
            'product' => [
                'name' => $item->productVariant->product->name,
                'stock' => $item->productVariant->stock,
                'min' => $item->product->min_cart,
                'max' => $item->product->max_cart,
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    private function isStockInsufficient($item)
    {
        return ! is_null($item->productVariant->stock) && $item->quantity > $item->productVariant->stock;
    }

    public function store(StoreUserOrderRequest $request)
    {
        $user = Auth::user();
        $cartItems = $user->cart->load(['product', 'productPrice', 'productVariant', 'productVariant.items', 'productPrice.specialPrices.price']);
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], Response::HTTP_BAD_REQUEST);
        }

        $stockCheck = $this->checkStock($cartItems);
        if ($stockCheck !== true) {
            return $stockCheck;
        }

        $sendInfo = Cache::get($request->type);
        if (! $sendInfo) {
            return response()->json(['message' => 'invalid_shipping'], Response::HTTP_BAD_REQUEST);
        }

        $orderNumber = $this->generateUniqueOrderNumber();

        try {
            $order = DB::transaction(function () use ($cartItems, $sendInfo, $orderNumber, $request) {
                $order = $this->createOrder($orderNumber, $sendInfo, $request->address, $request->description);
                $this->addOrderItems($order, $cartItems);
                $this->updateOrderPrices($order, $cartItems);

                return $order;
            });

            $this->cartService->emptyCart();

            return (new UserOrderResource($order))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Failed to create order: '.$e->getMessage());

            return response()->json(['message' => 'Failed to create order.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return (new UserOrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function showByOrderNumber($orderNumber)
    {
        $order = UserOrder::with(['shipping', 'shipping.method', 'items', 'items.product'])->where('user_id', Auth::id())->byOrderNumber($orderNumber)->firstOrFail();
        $order->items->load('product.media');

        // Access feature image for each product
        foreach ($order->items as $item) {
            $tmp = json_decode($item->item_info, true);
            $item->product['feature_image'] = $item->product->getMedia('feature_image');
            $item['variant'] = $tmp['variant'];
            $item['price'] = $tmp['price'];
            unset($item->product['item_info']);
            unset($item->product['media']);
        }

        return (new UserOrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserOrderRequest $request, $orderNumber)
    {
        $order = UserOrder::with(['shipping', 'shipping.method', 'items', 'items.product', 'items.productVariant', 'items.productVariant.items', 'items.productPrice'])
            ->where('user_id', Auth::id())
            ->byOrderNumber($orderNumber)
            ->firstOrFail();

        if ($order->status !== OrderStatus::REGISTERED) {
            return response()->json(['message' => 'Failed to update order: order can not change at this point'], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $cartItems = $this->updateOrderItems($order);
                if (count($cartItems) === 0) {
                    $order->status = OrderStatus::CANCELLED;
                    $order->save();
                    $order->delete();
                }
                $this->updateOrderPrices($order, $cartItems);
                $order->update($request->all());
                $order->touch();
            });
            if ($order->status === OrderStatus::CANCELLED) {
                return response('order is not allowed')->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            return (new UserOrderResource($order))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserOrder $order)
    {
        //
    }

    /**
     * Generate a unique order number.
     */
    private function generateUniqueOrderNumber(): string
    {
        do {
            $orderNumber = strtoupper(Str::random(12));
        } while (UserOrder::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Create a new order.
     */
    private function createOrder(string $orderNumber, array $sendInfo, int $addressId, ?string $description = null): UserOrder
    {
        return UserOrder::create([
            'user_id' => Auth::id(),
            'user_address_id' => $addressId,
            'price' => 0,
            'price_with_discount' => 0,
            'order_number' => $orderNumber,
            'shipping_variant_id' => $sendInfo['variant']['id'],
            'shipment_price' => $sendInfo['price'],
            'description' => $description,
        ]);
    }

    /**
     * Add items to the order.
     */
    private function addOrderItems(UserOrder $order, $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'user_order_id' => $order->id,
                'product_variant_id' => $cartItem->product_variant_id,
                'quantity' => $cartItem->quantity,
                'item_info' => json_encode([
                    'variant' => $cartItem->productVariant,
                    'price' => $cartItem->productPrice,
                ]),
            ]);
        }
    }

    /**
     * Update info for items in order.
     */
    private function updateOrderItems(UserOrder $order)
    {
        $updatedItems = [];
        $itemsToDelete = [];

        foreach ($order->items as $item) {
            if (! $item->product->is_in_stock) {
                $item->quantity = 0;
            } else {
                $item->quantity = min(
                    $item->quantity,
                    $item->productVariant->stock,
                    $item->product->max_cart,
                    max($item->product->min_cart, $item->quantity)
                );
            }

            if ($item->quantity > 0) {
                $item->update([
                    'item_info' => json_encode([
                        'variant' => $item->productVariant,
                        'price' => $item->productPrice,
                    ]),
                ]);
                $updatedItems[] = $item;
            } else {
                $itemsToDelete[] = $item->id;
            }
        }

        if (! empty($itemsToDelete)) {
            OrderItem::destroy($itemsToDelete);
        }

        return $updatedItems;
    }

    /**
     * Update the order prices.
     */
    private function updateOrderPrices(UserOrder $order, $cartItems): void
    {
        $totalPrice = $cartItems->sum(function ($cartItem) {
            $userPrice = $this->productPriceService->calcUserPrice($cartItem->productPrice);

            return $userPrice->price * $cartItem->quantity;
        });

        $totalPriceWithDiscount = $cartItems->sum(function ($cartItem) {
            $userPrice = $this->productPriceService->calcUserPrice($cartItem->productPrice);

            return $userPrice->discounted_price == 0
                ? $userPrice->price * $cartItem->quantity
                : $userPrice->discounted_price * $cartItem->quantity;
        });

        $order->update([
            'price' => $totalPrice,
            'price_with_discount' => $totalPriceWithDiscount,
        ]);
    }
}

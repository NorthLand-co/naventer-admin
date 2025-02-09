<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ecommerce\ApplyCouponRequest;
use App\Models\Coupon;
use App\Models\UserOrder;
use App\Services\Api\CouponService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function apply(ApplyCouponRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Retrieve order by order number or order ID
        $order = $request->filled('order_number')
            ? UserOrder::byOrderNumber($request->order_number)->first()
            : UserOrder::find($request->order_id);

        // Handle case where order is not found
        if (! $order) {
            return $this->errorResponse('order', 'validation.exist', 404);
        }

        // Retrieve the coupon by code
        $coupon = Coupon::where('code', $request->code)->load(['locations', 'products', 'categories'])->first();

        // Handle case where coupon is not found
        if (! $coupon) {
            return $this->errorResponse('code', 'validation.exist', 404);
        }

        // Validate coupon for the specified order
        $validation = $this->couponService->validateCouponForOrder($coupon, $order);

        // Return validation error if any
        if (! empty($validation['error'])) {
            return $this->errorResponse('coupon', $validation['error'], 422);
        }

        // Calculate the discount based on coupon type
        $discount_price = $coupon->discount_type === 'percentage'
            ? min($coupon->max_discount_price, ($coupon->discount_value / 100) * $request->cart_total)
            : $coupon->discount_value;

        // Process the application of the coupon using a database transaction
        DB::transaction(function () use ($coupon, $user, $order, $discount_price) {
            $coupon->incrementUsage(); // Increment the coupon usage count

            // Attach coupon to the user if authenticated
            if ($user) {
                $user->coupons()->attach($coupon->id);
            }

            // Add the coupon and discount to the order
            $order->addCouponToOrder($coupon->code, $discount_price);
        });

        // Return success response
        return $this->successResponse($discount_price, $request->cart_total);
    }

    /**
     * Send error response in Laravel validation format.
     */
    protected function errorResponse($field, $message, $status)
    {
        return response()->json([
            'errors' => [
                $field => [$message],
            ],
        ], $status);
    }

    /**
     * Send success response with coupon applied details.
     */
    protected function successResponse($discount_price, $cart_total)
    {
        return response()->json([
            'message' => 'Coupon applied successfully.',
            'discount' => $discount_price,
            'new_total' => $cart_total - $discount_price,
        ], 200);
    }
}

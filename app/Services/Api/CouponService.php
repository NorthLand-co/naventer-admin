<?php

namespace App\Services\Api;

use App\Models\Coupon;
use App\Models\UserOrder;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    public function validateCouponForOrder(Coupon $coupon, UserOrder $order)
    {
        if (! $coupon->isActive()) {
            return ['error' => 'validation.inactive'];
        }

        if (! $coupon) {
            return response()->json(['error' => 'validation.code'], 404);
        }

        if ($coupon->isExpired()) {
            return response()->json(['error' => 'validation.date'], 400);
        }

        if (! $coupon->hasUsesLeft()) {
            return response()->json(['error' => 'validation.limit'], 400);
        }

        // Check if the coupon is for first-time buyers
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($coupon->is_first_time_only) {
            // Check if the user has made any purchases
            $hasPurchased = UserOrder::where('user_id', $user->id)->exists();

            if ($hasPurchased) {
                return response()->json(['error' => 'validation.first'], 403);
            }
        }

        // Check if user has already used this coupon
        if ($user && $user->coupons()->where('coupon_id', $coupon->id)->exists()) {
            return response()->json(['error' => 'validation.user'], 400);
        }

        // Check if coupon is restricted by location
        if ($coupon->locations()->exists() && ! $coupon->locations()->where('id', $order->location_id)->exists()) {
            return ['error' => 'validation.location'];
        }

        // Check if coupon is restricted by product
        $validProduct = $order->products->filter(function ($product) use ($coupon) {
            return $coupon->products->contains($product);
        });
        if ($coupon->products()->exists() && $validProduct->isEmpty()) {
            return ['error' => 'validation.product'];
        }

        // Check if coupon is restricted by category
        $validCategory = $order->products->filter(function ($product) use ($coupon) {
            return $coupon->categories->contains($product->category);
        });
        if ($coupon->categories()->exists() && $validCategory->isEmpty()) {
            return ['error' => 'validation.category'];
        }

        return ['success' => 'Coupon is valid.'];
    }
}

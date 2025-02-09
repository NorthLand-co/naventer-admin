<?php

namespace App\Services\Api;

use App\Models\UserCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\QueryBuilder\QueryBuilder;

class UserCartService
{
    protected $userId;

    protected $sessionId;

    public function __construct()
    {
        $this->userId = Auth::check() ? Auth::id() : null;
        $this->sessionId = Session::get('temporary_token') ?? null;
    }

    public function getUserCartList()
    {
        $sessionId = $this->sessionId;
        $userId = $this->userId;

        return UserCart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
        })
            ->get();
        // return QueryBuilder::for(UserCart::class)
        //     ->where(function ($query) use ($sessionId, $userId) {
        //         $query->where('session_id', $sessionId);
        //         if ($userId) {
        //             $query->orWhere('user_id', $userId);
        //         }
        //     })
        //     ->allowedFilters(['product_name', 'price'])
        //     ->allowedIncludes(['product', 'productVariant', 'productVariant.items', 'productVariant.items.variant', 'productPrice'])
        //     ->get();
    }

    public function getUserCartProduct($id = null)
    {
        $userId = $this->userId;
        $sessionId = $this->sessionId;

        return UserCart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
        })
            ->where('product_variant_id', $id)
            ->first();
    }

    public function getUserCartById($userCartId)
    {
        $userId = $this->userId;
        $sessionId = $this->sessionId;
        $id = $userCartId ?? null;

        return UserCart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
        })
            ->where('id', $id)
            ->firstOrFail();
    }

    public function removeFromCartById($id)
    {
        $userId = $this->userId;
        $sessionId = $this->sessionId;

        $cartItem = UserCart::where(function ($query) use ($sessionId, $userId) {
            $query->where('session_id', $sessionId);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
        })
            ->where('id', $id)
            ->first();

        $cartItem->delete();
    }

    /**
     * Empty the user's cart.
     */
    public function emptyCart()
    {
        $user = Auth::user();
        $cartItems = $user->cart;
        if ($user && $cartItems) {
            foreach ($cartItems as $key => $cartItem) {
                $cartItem->delete();
            }
        }
    }
}

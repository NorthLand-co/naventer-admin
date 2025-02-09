<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\UserOrder;
use App\Notifications\AdminNotification;
use App\Notifications\UserOrderNotification;

class UserOrderObserver
{
    /**
     * Handle the UserOrder "created" event.
     */
    public function created(UserOrder $userOrder): void
    {
        //
    }

    /**
     * Handle the UserOrder "updated" event.
     */
    public function updated(UserOrder $userOrder): void
    {
        if ($userOrder->status === OrderStatus::PAID) {
            $order = $userOrder->load(['items.product', 'user', 'address.city', 'address.state']);
            foreach ($order->items as $key => $item) {
                if (! is_null($item->productVariant->stock)) {
                    $item->productVariant->stock -= $item->quantity;
                    $item->productVariant->save();
                }
            }
            $order->notify(new AdminNotification(['order' => $order], 'emails.admin.user-order-paid'));
            // $order->notify(new UserOrderNotification("نرس‌لند\n{$order->user->name} عزیز سفارش شما ثبت شد\nشماره سفارش: {$order->order_number}"));
        }
    }

    /**
     * Handle the UserOrder "deleted" event.
     */
    public function deleted(UserOrder $userOrder): void
    {
        //
    }

    /**
     * Handle the UserOrder "restored" event.
     */
    public function restored(UserOrder $userOrder): void
    {
        //
    }

    /**
     * Handle the UserOrder "force deleted" event.
     */
    public function forceDeleted(UserOrder $userOrder): void
    {
        //
    }
}

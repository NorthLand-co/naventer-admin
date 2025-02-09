<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\CRM\Call;
use App\Observers\UserOrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

#[ObservedBy([UserOrderObserver::class])]
class UserOrder extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_address_id', 'order_number', 'price', 'price_with_discount', 'status', 'coupon', 'coupon_price', 'shipping_variant_id', 'shipment_price', 'description'];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    // Relations
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id', 'id');
    }

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(ShippingVariant::class, 'shipping_variant_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function info(): HasMany
    {
        return $this->hasMay(UserOrderInfo::class);
    }

    public function calls(): MorphMany
    {
        return $this->morphMany(Call::class, 'callable');
    }

    // Scopes
    public function scopeByOrderNumber($query, $orderNumber)
    {
        return $query->where('order_number', $orderNumber);
    }

    // Accessor
    public function getFinalPriceAttribute()
    {
        return $this->price_with_discount + ($this->shipment_price ?? 0) - ($this->coupon_price ?? 0);
    }

    public function getPaymentableIdAttribute()
    {
        return $this->order_number;
    }

    // Add coupons to order
    public function addCouponToOrder(string $code, int $price): UserOrder
    {
        // Assign the coupon code and coupon price to the model attributes
        $this->coupon = $code;
        $this->coupon_price = $price;

        // Save the changes to the database
        $this->save();

        return $this;
    }

    /**
     * Route notifications for the SMS channel.
     *
     * @return array<string, string>|string
     */
    public function routeNotificationForKavenegar($driver, $notification = null)
    {
        return $this->user->phone;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return array<string, string>|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        $orderManagers = User::role('order_manager')->get()->pluck('name', 'email')->toArray();

        return $orderManagers;
    }
}

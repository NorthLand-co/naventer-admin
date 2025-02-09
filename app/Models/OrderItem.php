<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

class OrderItem extends Model
{
    use HasFactory, TraitsBelongsToThrough;

    protected $fillable = ['user_order_id', 'product_variant_id', 'item_info', 'quantity'];

    protected $casts = [
        'item_info' => 'json',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UserOrder::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productPrice(): BelongsToThrough
    {
        return $this->belongsToThrough(ProductPrice::class, ProductVariant::class);
    }

    public function product(): BelongsToThrough
    {
        return $this->belongsToThrough(Product::class, [ProductPrice::class, ProductVariant::class]);
    }
}

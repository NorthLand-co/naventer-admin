<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

class UserCart extends Model
{
    use TraitsBelongsToThrough;

    protected $fillable = ['session_id', 'user_id', 'product_variant_id', 'quantity'];

    /**
     * Define relationship through the product variant to access the Product model.
     */
    public function product(): BelongsToThrough
    {
        return $this->belongsToThrough(Product::class, [ProductPrice::class, ProductVariant::class]);
    }

    /**
     * Define relationship with the ProductVariant model.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productPrice(): BelongsToThrough
    {
        return $this->belongsToThrough(ProductPrice::class, ProductVariant::class);
    }

    /**
     * Define relationship with the User model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

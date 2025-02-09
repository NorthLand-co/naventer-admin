<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialPrices extends Model
{
    use HasFactory;

    protected $fillable = ['product_price_id', 'special_prices_group_id', 'price_details'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecialPricesGroup::class, 'special_prices_group_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'price_details');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
    }
}

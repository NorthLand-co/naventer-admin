<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariantItems extends Model
{
    use HasFactory;

    protected $fillable = ['product_variant_id', 'category_variant_id'];

    public function variant(): HasOne
    {
        return $this->hasOne(CategoryVariant::class, 'id', 'category_variant_id');
    }
}

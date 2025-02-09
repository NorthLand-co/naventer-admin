<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Relations\BelongsToThrough as RelationsBelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough;

class ProductVariant extends Model implements HasMedia
{
    use BelongsToThrough;
    use HasFactory;
    use HasUuid;
    use InteractsWithMedia;

    protected $fillable = ['product_price_id', 'sku', 'value', 'stock'];

    protected $appends = ['thumb'];

    public function items(): HasMany
    {
        return $this->hasMany(ProductVariantItems::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class, 'product_price_id');
    }

    public function product(): RelationsBelongsToThrough
    {
        return $this->belongsToThrough(Product::class, ProductPrice::class);
    }

    public function getThumbAttribute()
    {
        return $this->thumb();
    }

    public function thumb(): ?Media
    {
        return $this->getFirstMedia('thumb');
    }
}

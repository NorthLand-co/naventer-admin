<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['value', 'name', 'icon', 'order', 'product_id', 'category_attribute_id'];

    protected static function booted()
    {
        static::addGlobalScope('orderedBy', function ($builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CategoryAttribute::class, 'category_attribute_id', 'id');
    }

    // Accessors
    public function getShowNameAttribute(): ?string
    {
        return $this->name ?? ($this->attribute->name ?? null);
    }

    public function getShowIconAttribute(): ?string
    {
        return $this->icon ?? ($this->attribute->icon ?? null);
    }

    public function getShowTypeAttribute(): ?string
    {
        return $this->attribute->type ?? null;
    }

    public function getShowOrderAttribute(): ?string
    {
        return $this->order === 255 ? ($this->attribute->order ?? 255) : $this->order;
    }
}

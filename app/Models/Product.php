<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasTags;
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    protected $fillable = ['category_id', 'keywords', 'name', 'slug', 'sku', 'barcode', 'type', 'about', 'description', 'details', 'is_in_stock', 'is_activated', 'is_shipped', 'is_trend', 'has_options', 'has_multi_price', 'has_unlimited_stock', 'has_max_cart', 'min_cart', 'max_cart', 'has_stock_alert', 'min_stock_alert', 'max_stock_alert', 'created_at', 'updated_at', 'order', 'color'];

    protected $casts = [
        'is_in_stock' => 'boolean',
        'is_activated' => 'boolean',
        'is_shipped' => 'boolean',
        'is_trend' => 'boolean',
        'has_options' => 'boolean',
        'has_multi_price' => 'boolean',
        'has_unlimited_stock' => 'boolean',
        'has_max_cart' => 'boolean',
        'has_stock_alert' => 'boolean',
    ];

    // protected $appends = ['thumb'];

    public static array $allowedIncludes = ['prices', 'category', 'tags'];

    public static array $allowedFilters = ['name', 'category.slug'];

    protected static function booted()
    {
        static::addGlobalScope('orderedBy', function ($builder) {
            $builder->orderBy('order', 'desc');
        });
    }

    // Scopes
    /**
     * Apply the default sort order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderedBy($query, $column = 'order', $direction = 'desc')
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * Scope a query to find a model by its slug.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindBySlug($query, $value)
    {
        return $query->where('slug', $value);
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function shipping(): BelongsToMany
    {
        return $this->belongsToMany(ShippingVariant::class, 'product_shippings');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function faqs()
    {
        return $this->morphMany(FAQ::class, 'faqable');
    }

    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(RecommendationAnswer::class, 'recommendation_product_answers');
    }

    // Getters
    public function getThumbAttribute()
    {
        return $this->thumb();
    }

    public function getBackgroundAttribute()
    {
        return $this->background();
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('feature_image')
            ->useDisk('s3');
    }

    public function thumb(): ?Media
    {
        return $this->getFirstMedia('feature_image');
    }

    public function background(): ?Media
    {
        return $this->getFirstMedia('background_image');
    }

    // Searchable

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'about' => $this->about,
            'description' => $this->description,
            'details' => $this->details,
        ];

        return $array;
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'products_index';
    }
}

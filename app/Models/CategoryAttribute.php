<?php

namespace App\Models;

use App\Enums\CategoryAttributeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoryAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'type', 'values', 'order'];

    protected $casts = [
        'type' => CategoryAttributeType::class,
        'values' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('orderedBy', function ($builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_category_attribute');
    }
}

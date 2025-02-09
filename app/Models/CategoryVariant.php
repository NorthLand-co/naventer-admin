<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoryVariant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'category_id'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_category_variant');
    }
}

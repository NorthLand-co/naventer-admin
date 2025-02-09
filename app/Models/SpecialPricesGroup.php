<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialPricesGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public $timestamps = false;

    public function prices(): HasMany
    {
        return $this->hasMany(SpecialPrices::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_special_prices_group');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'discount_type', 'discount_value', 'max_discount_price', 'max_uses', 'uses', 'expires_at'];

    protected $dates = ['expires_at'];

    // Relations
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'coupon_location');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    // Check if coupon is Active
    public function isActive()
    {
        return $this->active && now()->isBefore($this->expires_at);
    }

    // Check if coupon has remaining uses
    public function hasUsesLeft()
    {
        return is_null($this->max_uses) || $this->uses < $this->max_uses;
    }

    // Increment usage
    public function incrementUsage()
    {
        $this->increment('uses');
    }
}

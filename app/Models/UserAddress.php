<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'address', 'city_id', 'state_id', 'postal_code', 'country_id', 'phone_number', 'lat', 'long', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'id'; // If you are using 'slug' or any other column for lookup
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): HasOne
    {
        return $this->hasOne(LocationCity::class, 'id', 'city_id');
    }

    public function state(): HasOne
    {
        return $this->hasOne(Location::class, 'id', 'state_id');
    }

    public function country(): HasOne
    {
        return $this->hasOne(Location::class, 'id', 'country_id');
    }
}

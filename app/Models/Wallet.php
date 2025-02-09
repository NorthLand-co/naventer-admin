<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;

class Wallet extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = ['user_id', 'balance', 'credit'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTotalAttribute()
    {
        return $this->balance + $this->credit;
    }
}

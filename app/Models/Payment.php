<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['paymentable_id', 'paymentable_type', 'transaction_id', 'amount', 'method', 'status', 'details', 'bank_transaction_id'];

    protected $casts = [
        'details' => 'json',
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
    ];

    protected $hidden = ['bank_transaction_id'];

    protected static function booted()
    {
        static::addGlobalScope('defaultOrder', function (Builder $builder) {
            $builder->orderBy('created_at', 'asc');
        });
    }

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}

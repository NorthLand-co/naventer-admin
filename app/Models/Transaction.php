<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;

class Transaction extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = ['wallet_id', 'amount', 'type', 'bank_transaction_id', 'payment_transaction_id', 'details', 'status'];

    protected $casts = [
        'details' => 'json',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    protected $hidden = ['bank_transaction_id'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}

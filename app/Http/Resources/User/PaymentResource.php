<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paymentable_id' => $this->paymentable_id,
            'paymentable_type' => $this->paymentable_type,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'method' => $this->method->name,
            'status' => $this->status->name,
            'details' => $this->details,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

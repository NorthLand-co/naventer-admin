<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['type'] = $this->type->label();
        $data['status'] = $this->status->label();
        if ($this->payment) {
            $data['payment'] = new PaymentResource($this->payment);
        }

        return $data;
    }
}

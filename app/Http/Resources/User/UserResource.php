<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->relationLoaded('profile')) {
            $data['profile'] = new ProfileResource($this->profile);
        }
        if ($this->relationLoaded('wallet')) {
            $data['wallet'] = new WalletResource($this->wallet);
        }

        return $data;
    }
}

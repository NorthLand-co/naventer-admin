<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locations = [
            'city' => $this->city->name,
            'state' => $this->state->name,
            'country' => $this->country->name,
        ];

        $data = parent::toArray($request);

        return array_merge($data, $locations);
    }
}

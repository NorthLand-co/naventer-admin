<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
        ];

        if ($this->relationLoaded('user')) {
            $data['user'] = new UserResource($this->user);
        }

        return $data;
    }
}

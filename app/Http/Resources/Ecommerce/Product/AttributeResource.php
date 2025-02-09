<?php

namespace App\Http\Resources\Ecommerce\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->showName,
            'value' => $this->value,
            'icon' => $this->showIcon,
            'order' => $this->showOrder,
            'type' => $this->type,
        ];
    }
}

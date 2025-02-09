<?php

namespace App\Http\Resources\Ecommerce\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $items = VariantItemsResource::collection($this->items);
        $data = [
            'id' => $this->uuid,
            'items' => $items,
            'thumb' => $this->getMedia('thumb'),
        ];

        return $data;
    }
}

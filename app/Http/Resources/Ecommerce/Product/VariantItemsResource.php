<?php

namespace App\Http\Resources\Ecommerce\Product;

use App\Http\Resources\Ecommerce\Category\VariantsResource as CategoryVariantsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $variant = new CategoryVariantsResource($this->variant);
        $data = [
            'value' => $this->value,
        ];

        if (! is_null($variant) && ! empty($variant->name)) {
            $data['name'] = $variant?->name;
            $data['icon'] = $variant?->icon;
        }

        return $data;
    }
}

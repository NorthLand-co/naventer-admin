<?php

namespace App\Http\Resources\Ecommerce\Category;

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
        // return parent::toArray($request);
        $data = [
            'name' => $this->name,
            'icon' => $this->icon,
            'type' => $this->type,
            'order' => $this->order,
        ];

        if ($this->values) {
            $data['values'] = explode(',', $this->values);
        }

        return $data;
    }
}

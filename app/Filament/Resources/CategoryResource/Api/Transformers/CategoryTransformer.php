<?php

namespace App\Filament\Resources\CategoryResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        // Get the 'include' parameter from the request and convert it to an array
        $includes = explode(',', $request->query('include', ''));

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
        ];

        // Check if the request contains a parameter to include children or parent
        if (in_array('children', $includes) && count($this->children)) {
            // dd($this, $this->children);
            $data['children'] = CategoryTransformer::collection($this->children);
        }
        if (! is_null($this->parent_category_id) && in_array('parent', $includes)) {
            $category = new CategoryTransformer($this->parent);
            $data['parent'] = $category;
        }

        return $data;
    }
}

<?php

namespace App\Http\Resources\Ecommerce\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    protected $forceIncludeParent = false;

    protected $forceIncludeChildren = false;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  bool  $forceIncludeParent
     */
    public function __construct($resource, $forceIncludeParent = false, $forceIncludeChildren = false)
    {
        parent::__construct($resource);
        $this->forceIncludeParent = $forceIncludeParent;
        $this->forceIncludeChildren = $forceIncludeChildren;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $includes = explode(',', $request->query('include', ''));

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'thumb' => $this->thumb,
        ];

        if ($this->whenLoaded('seo')) {
            $data['seo'] = $this->seo;
        }

        // Check if the request contains a parameter to include children or parent
        if ($this->forceIncludeChildren || (! is_null($this->parent_category_id) && in_array('children', $includes))) {
            $data['children'] = CategoryResource::collection($this->children);
        }

        if ($this->forceIncludeParent || (! is_null($this->parent_category_id) && in_array('parent', $includes))) {
            $category = new CategoryResource($this->parent, $this->forceIncludeParent);
            $data['parent'] = $category;
        }

        return $data;
    }
}

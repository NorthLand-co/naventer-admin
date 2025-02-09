<?php

namespace App\Filament\Resources\BlogPostResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge($this->resource->toArray(), [
            'thumb' => $this->thumb,
        ]);
    }
}

<?php

namespace App\Filament\Resources\BannerResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'slug' => $this->slug,
            'url' => $this->url,
            'alt' => $this->alt,
            'description' => $this->description,
            'styles' => $this->styles,
            'target' => $this->target,
            'class' => $this->class,
            'order' => $this->order,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
            'media' => [
                'image' => $this->getFirstMedia('image'),
                'responsive_image' => $this->getFirstMedia('responsive_image'),
            ],
        ];
    }
}

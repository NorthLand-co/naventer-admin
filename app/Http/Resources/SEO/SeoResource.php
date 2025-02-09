<?php

namespace App\Http\Resources\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'social' => $this->social,
            'keywords' => $this->keywords,
            'image' => $this->getMedia('image'),
            'robots' => $this->robots,
            'canonical_url' => $this->canonical_url,
            'og_title' => $this->og_title,
            'og_description' => $this->og_description,
            'og_image' => $this->getMedia('og_image'),
        ];
    }
}

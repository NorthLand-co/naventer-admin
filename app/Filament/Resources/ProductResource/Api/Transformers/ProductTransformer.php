<?php

namespace App\Filament\Resources\ProductResource\Api\Transformers;

use App\Http\Resources\Api\Comments\CommentResource;
use App\Http\Resources\Ecommerce\Category\CategoryResource;
use App\Http\Resources\Ecommerce\Product\AttributeResource;
use App\Http\Resources\Ecommerce\Product\PriceResource;
use App\Http\Resources\SEO\SeoResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $includes = $this->parseIncludes($request);

        // Base transformation array
        $data = [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'color' => $this->color,
            'about' => $this->about,
            'details' => $this->details,
            'is_activated' => $this->is_activated,
            'is_in_stock' => $this->is_in_stock,
            'is_shipped' => $this->is_shipped,
            'is_trend' => $this->is_trend,
            'has_options' => $this->has_options,
            'has_multi_price' => $this->has_multi_price,
            'has_unlimited_stock' => $this->has_unlimited_stock,
            'has_max_cart' => $this->has_max_cart,
            'min_cart' => $this->min_cart,
            'max_cart' => $this->max_cart,
            'has_stock_alert' => $this->has_stock_alert,
            'min_stock_alert' => $this->min_stock_alert,
            'max_stock_alert' => $this->max_stock_alert,
            'feature_image' => $this->getMedia('feature_image'),
            'background_image' => $this->getMedia('background_image'),
        ];

        // Add optional includes based on the request parameters
        $this->addOptionalIncludes($data, $includes);

        return $data;
    }

    /**
     * Parse and return the 'include' parameter as an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    private function parseIncludes($request): array
    {
        return explode(',', $request->query('include', ''));
    }

    /**
     * Add optional includes to the data array.
     */
    private function addOptionalIncludes(array &$data, array $includes): void
    {
        if (in_array('description', $includes)) {
            $data['description'] = $this->description;
        }

        if (in_array('tags', $includes)) {
            $data['tags'] = $this->tags;
        }

        if (in_array('gallery', $includes)) {
            $data['gallery'] = $this->getMedia('gallery');
        }

        if (in_array('category', $includes)) {
            $data['category'] = new CategoryResource($this->whenLoaded('category'));
        }

        if (in_array('attributes', $includes)) {
            $data['attributes'] = AttributeResource::collection($this->whenLoaded('attributes'));
        }

        if (in_array('comments', $includes) && $this->relationLoaded('comments')) {
            $comments = $this->comments->load(['user', 'replies']);
            $data['comments'] = CommentResource::collection($comments);
        }

        if (in_array('seo', $includes) && $this->relationLoaded('seo')) {
            $data['seo'] = new SeoResource($this->seo);
        }

        if (in_array('faq', $includes) && $this->relationLoaded('faqs')) {
            $data['faqs'] = $this->faqs;
        }

        if (in_array('prices', $includes) && $this->relationLoaded('prices')) {
            $data['prices'] = PriceResource::collection($this->prices);
        } else {
            $this->addLowestPrice($data);
        }
    }

    /**
     * Add the lowest price or discounted price to the data array if available.
     */
    private function addLowestPrice(array &$data): void
    {
        $lowestPrice = $this->prices->sortBy('price')->first();

        if (Auth::check()) {
            $userSpecialGroups = Auth::user()->specialPricesGroups->pluck('id')->toArray();
            $specialPrice = $lowestPrice->specialPrices
                ->whereIn('special_prices_group_id', $userSpecialGroups)
                ->sortBy('price')
                ->first();

            if ($specialPrice) {
                $lowestPrice = $specialPrice->price;
            }
        }

        if ($lowestPrice) {
            $data['price'] = $lowestPrice->price;
            $this->addDiscountedPrice($data, $lowestPrice);
        }
    }

    /**
     * Add discounted price information to the data array if applicable.
     *
     * @param  \App\Models\Price  $lowestPrice
     */
    private function addDiscountedPrice(array &$data, $lowestPrice): void
    {
        if (
            $lowestPrice->discounted_price &&
            (! $lowestPrice->discounted_to || $lowestPrice->discounted_to->isFuture())
        ) {
            $data['discounted_price'] = $lowestPrice->discounted_price;
        }
    }
}

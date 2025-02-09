<?php

namespace App\Http\Resources\Ecommerce\Cart;

use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Http\Resources\Ecommerce\Product\PriceResource;
use App\Http\Resources\Ecommerce\Product\VariantsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCartResource extends JsonResource
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
            'id' => $this->id,
            'quantity' => $this->productVariant->product->is_in_stock ? $this->quantity : 0,
            'variant' => new VariantsResource($this->productVariant),
            'price' => new PriceResource($this->productVariant->price),
            'product' => new ProductTransformer($this->productVariant->product),
        ];
    }
}

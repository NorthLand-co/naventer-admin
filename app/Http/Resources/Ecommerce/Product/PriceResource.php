<?php

namespace App\Http\Resources\Ecommerce\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid' => $this->uuid,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'discounted_to' => $this->discounted_to,
            'vat' => $this->vat,
            'max_cart' => $this->max_cart,
            'image' => $this->getMedia('thumb'),
        ];

        $includes = explode(',', $request->query('include', ''));

        // Check if the request contains a parameter to include variants
        if ((! is_null($this->variants) && in_array('variants', $includes))) {
            $data['variants'] = VariantsResource::collection($this->variants);
        }

        // Check if the request contains a parameter to include user special prices
        if ((in_array('user_price', $includes) && ! is_null($this->specialPrices) && Auth::check())) {
            $userSpecialGroups = Auth::user()->specialPricesGroups->pluck('id')->toArray();

            $specialPrices = $this->specialPrices;

            $filteredPrices = $specialPrices->filter(function ($item) use ($userSpecialGroups) {
                return in_array($item['special_prices_group_id'], $userSpecialGroups);
            })->load('price')->pluck('price')->toArray();

            if (count($filteredPrices)) {
                $data['user_price'] = $filteredPrices;
            }
        }

        return $data;
    }
}

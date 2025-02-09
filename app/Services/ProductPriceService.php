<?php

namespace App\Services;

use App\Models\ProductPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProductPriceService
{
    public function getSpecialPrices(Collection $productPrices): Collection
    {
        if (Auth::check()) {
            $userSpecialGroups = Auth::user()->specialPricesGroups->pluck('id')->toArray();

            return $productPrices->map(function ($productPrice) use ($userSpecialGroups) {
                $specialPrices = $productPrice->specialPrices;

                // Filter special prices by user's groups and load 'price' relation
                $filteredPrices = $specialPrices->filter(function ($item) use ($userSpecialGroups) {
                    return in_array($item['special_prices_group_id'], $userSpecialGroups);
                })->load('price');

                // Return the first filtered price or the lowest price if none match
                $productPrice->lowest_price = $filteredPrices->pluck('price')->first() ?? $productPrice->price;

                return $productPrice;
            });
        }

        // If the user is not logged in, return the product prices as is
        return $productPrices;
    }

    public function calcUserPrice(ProductPrice $price): ProductPrice
    {
        if (! $price->specialPrices) {
            return $price;
        } elseif (Auth::check()) {
            $userSpecialGroups = Auth::user()->specialPricesGroups->pluck('id')->toArray();
            $specialPrices = $price->specialPrices;

            // Filter and sort special prices by user's groups and load 'price' relation
            $filteredPrice = $specialPrices->filter(function ($item) use ($userSpecialGroups) {
                return in_array($item['special_prices_group_id'], $userSpecialGroups);
            })
                ->sortBy(function ($item) {
                    return $item['price']['price'];
                })
                ->first();
            if (is_null($filteredPrice)) {
                return $price;
            }

            return $filteredPrice->price;
        } else {
            abort(401);
        }
    }
}

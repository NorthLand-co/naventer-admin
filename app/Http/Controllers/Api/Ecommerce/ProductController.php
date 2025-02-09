<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Enums\OrderStatus;
use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\Product\PriceResource;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function bestSellers()
    {
        $cacheKey = 'best_selling_products_list';
        $cacheDuration = now()->addHours(36);

        $bestSellingProducts = Cache::remember($cacheKey, $cacheDuration, function () {
            // Aggregate order items to find top selling products
            $topSellingProductsData = OrderItem::query()
                ->select('products.id as product_id', Product::raw('SUM(order_items.quantity) AS total_quantity_sold'))
                ->join('user_orders', 'order_items.user_order_id', '=', 'user_orders.id')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->join('product_prices', 'product_variants.product_price_id', '=', 'product_prices.id')
                ->join('products', 'product_prices.product_id', '=', 'products.id')
                ->whereIn('user_orders.status', [OrderStatus::PAID, OrderStatus::PROCESSING, OrderStatus::SHIPPED, OrderStatus::DELIVERED])
                ->groupBy('products.id')
                ->orderByDesc('total_quantity_sold')
                ->limit(15)
                ->get();
            // Retrieve detailed product information
            $productIds = $topSellingProductsData->pluck('product_id');

            $productsCollection = Product::whereIn('id', $productIds)->get();

            // Sort the products based on total_quantity_sold from the salesCollection
            $sortedProducts = $productsCollection->sortByDesc(function ($product) use ($topSellingProductsData) {
                return $topSellingProductsData->firstWhere('product_id', $product['product_id'])['total_quantity_sold'] ?? 16;
            });

            return ProductTransformer::collection($sortedProducts);

        });

        // Transform and return the response with a 202 Accepted status
        return $bestSellingProducts
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function trends()
    {
        $cacheKey = 'trend_products';
        $cacheDuration = now()->addHours(24);

        $bestSellingProducts = Cache::remember($cacheKey, $cacheDuration, function () {
            $products = Product::where('is_trend', 1)->limit(5)->get();

            return ProductTransformer::collection($products);
        });

        return $bestSellingProducts
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function specialPrices(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_slug' => 'required|max:255|exists:products,slug',
        ]);

        if ($validator->fails() || ! Auth::check()) {
            abort(403, 'you don\'t have this permission');
        }

        $user = Auth::user();
        $userSpecialGroups = $user->specialPricesGroups->pluck('id')->toArray();

        $product = Product::with([
            'prices.specialPrices' => function ($query) use ($userSpecialGroups) {
                $query->whereIn('special_prices_group_id', $userSpecialGroups);
            },
            'prices.specialPrices.price',
            'prices.specialPrices.group',
            'prices.specialPrices.parent',
        ])->where('slug', $request->product_slug)->first();

        if ($product) {
            $specialPrices = $product->prices->flatMap(function ($price) {
                return $price->specialPrices;
            })->map(function ($specialPrice) {
                return $specialPrice->toArray();
            })->toArray();
        } else {
            $specialPrices = [];
        }

        return response($specialPrices)->setStatusCode(Response::HTTP_OK);
    }

    public function getPrices(string $slug)
    {
        $product = Product::with(['attributes', 'prices.specialPrices'])
            ->where('slug', $slug)
            ->first();

        if ($product) {
            return PriceResource::collection($product->prices->load(['variants.items']))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        }

        return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
    }
}

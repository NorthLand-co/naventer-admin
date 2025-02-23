<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Filament\Resources\CategoryResource\Api\Transformers\CategoryTransformer;
use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Ecommerce\Category\CategoryAttributeResource;
use App\Http\Resources\Ecommerce\Category\CategoryResource;
use App\Http\Transformer\Api\PaginationTransformer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\Sorts\Sort;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends ApiController
{
    /**
     * Retrieve a list of top-level categories.
     */
    public function list(): JsonResponse
    {
        try {
            $categories = Category::whereNull('parent_category_id')->get();
            $transformedCategories = CategoryTransformer::collection($categories);

            return response()->json($transformedCategories, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error retrieving top-level categories: '.$e->getMessage());

            return $this->errorResponse('Unable to retrieve categories.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve and return a specific category by its slug.
     */
    public function filters(Category $category): JsonResponse
    {
        try {
            $transformedCategory = CategoryAttributeResource::collection($category->attributes);

            return response()->json($transformedCategory, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error retrieving category attributes: '.$e->getMessage());

            return $this->errorResponse('Unable to retrieve category attributes.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve and return products for a specific category by its slug,
     * including products from its child categories.
     */
    public function products(Category $category, Request $request): JsonResponse
    {
        try {
            $category = $category->load(['seo']);
            // Get the category IDs for the parent category and its children
            $categoryIds = $category->children()->pluck('id')->toArray();
            $categoryIds[] = $category->id;

            // Get the current page and per page values from request
            $currentPage = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            // Build the query using Spatie QueryBuilder
            $query = QueryBuilder::for(Product::class)
                ->with(['media', 'category', 'attributes', 'comments.user', 'comments.replies', 'seo', 'prices.specialPrices'])
                ->whereIn('category_id', $categoryIds)
                ->allowedFields(['id', 'name', 'price', 'description', 'category_id'])
                ->allowedSorts(['name', 'is_in_stock', 'created_at', 'order', AllowedSort::custom('lowest_price', new LowestPriceSort)])
                ->allowedFilters([
                    AllowedFilter::exact('category_id'),
                    AllowedFilter::partial('name'),
                    AllowedFilter::partial('description'),
                ])
                ->allowedIncludes(['gallery', 'tags']);

            // Paginate the results
            $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

            // Transform the results
            $products = ProductTransformer::collection($paginator->items());

            $data = [
                'data' => [
                    'products' => $products,
                    'category' => new CategoryResource($category, true),
                ],
                'pagination' => PaginationTransformer::transform($paginator),
            ];

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error retrieving products for category: '.$e->getMessage());

            return response()->json([
                'message' => 'Unable to retrieve products for the specified category.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

class LowestPriceSort implements Sort
{
    public function __invoke(Builder $query, $descending, string $property)
    {
        $direction = $descending ? 'desc' : 'asc';

        $query->addSelect([
            'lowest_price' => DB::table('product_prices')
                ->select('price')
                ->whereColumn('product_prices.product_id', 'products.id')
                ->orderBy('price', 'asc')
                ->limit(1),
        ]);

        $query->orderBy('lowest_price', $direction);
    }
}

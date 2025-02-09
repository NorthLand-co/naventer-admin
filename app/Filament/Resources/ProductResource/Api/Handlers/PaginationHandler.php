<?php

namespace App\Filament\Resources\ProductResource\Api\Handlers;

use App\Filament\Resources\ProductResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static bool $public = true;

    public static ?string $uri = '/';

    public static ?string $resource = ProductResource::class;

    public function handler()
    {
        $query = static::getEloquentQuery();
        $model = static::getModel();
        $query = QueryBuilder::for($query)
            ->with(['media', 'category', 'attributes', 'comments.user', 'comments.replies', 'seo', 'prices.specialPrices'])
            ->allowedFields($model::$allowedFields ?? [])
            ->allowedSorts($model::$allowedSorts ?? [])
            ->allowedFilters($model::$allowedFilters ?? [])
            ->allowedIncludes($model::$allowedIncludes ?? null)
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}

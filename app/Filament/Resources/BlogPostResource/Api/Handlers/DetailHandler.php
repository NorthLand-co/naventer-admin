<?php

namespace App\Filament\Resources\BlogPostResource\Api\Handlers;

use App\Filament\Resources\BlogPostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class DetailHandler extends Handlers
{
    public static bool $public = true;

    public static ?string $uri = '/{id}';

    public static ?string $resource = BlogPostResource::class;

    public function handler(Request $request)
    {
        $id = $request->route('id');

        $query = static::getEloquentQuery();

        $model = static::getModel();

        // Log the allowed includes for debugging
        Log::info('Allowed Includes: ', $model::$allowedIncludes ?? []);

        $query = QueryBuilder::for(
            $query->where('slug', $id)
        )
            ->allowedFields($model::$allowedFields ?? [])
            ->allowedSorts($model::$allowedSorts ?? [])
            ->allowedFilters($model::$allowedFilters ?? []);

        // Apply includes only if they are not empty
        if (!empty($model::$allowedIncludes)) {
            $query->allowedIncludes($model::$allowedIncludes);
        }

        $result = $query->first();

        if (! $result) {
            return static::sendNotFoundResponse();
        }

        $transformer = static::getApiTransformer();

        return new $transformer($result);
    }
}

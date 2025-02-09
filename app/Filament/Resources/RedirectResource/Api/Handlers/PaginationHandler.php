<?php

namespace App\Filament\Resources\RedirectResource\Api\Handlers;

use App\Filament\Resources\RedirectResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static bool $public = true;

    public static ?string $uri = '/';

    public static ?string $resource = RedirectResource::class;

    public function handler()
    {
        $query = static::getEloquentQuery();
        $model = static::getModel();

        $query = QueryBuilder::for($query)
            ->allowedFields($this->getAllowedFields() ?? [])
            ->allowedSorts($this->getAllowedSorts() ?? [])
            ->allowedFilters($this->getAllowedFilters() ?? [])
            ->allowedIncludes($this->getAllowedIncludes() ?? [])
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        // $result = static::getApiTransformer()::collection($query);
        // Transform the collection and restructure it
        $data = static::getApiTransformer()::collection($query)->toArray(request());
        $result = [];

        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                $result[$key] = $value;
            }
        }

        return response()->json(['data' => $result]);
    }
}

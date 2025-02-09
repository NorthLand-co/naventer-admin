<?php

namespace App\Filament\Resources\ProductResource\Api\Handlers;

use App\Filament\Resources\ProductResource;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class DetailHandler extends Handlers
{
    public static bool $public = true;

    public static ?string $uri = '/{value}';

    public static ?string $resource = ProductResource::class;

    public function handler(Request $request)
    {
        $value = $request->route('value');

        $query = static::getEloquentQuery()
            ->with(['media', 'category', 'attributes', 'comments.user', 'comments.replies', 'seo', 'prices.specialPrices', 'faqs']);

        // Determine the field to query by
        $field = $this->determineField($value);

        $query = QueryBuilder::for(
            $query->where($field, $value)
        )->first();

        if (! $query) {
            return static::sendNotFoundResponse();
        }

        $transformer = static::getApiTransformer();

        return new $transformer($query);
    }

    /**
     * Determine the field to query by.
     */
    protected function determineField(string $value): string
    {
        // Add your logic to determine the field here.
        // For example, check if the value is numeric and use 'id', otherwise use 'slug'.
        return is_numeric($value) ? 'id' : 'slug';
    }
}

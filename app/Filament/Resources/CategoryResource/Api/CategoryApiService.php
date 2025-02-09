<?php

namespace App\Filament\Resources\CategoryResource\Api;

use App\Filament\Resources\CategoryResource;
use Rupadana\ApiService\ApiService;

class CategoryApiService extends ApiService
{
    protected static ?string $resource = CategoryResource::class;

    public static function handlers(): array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class,
        ];
    }
}

<?php

namespace App\Filament\Resources\BlogPostResource\Api;

use App\Filament\Resources\BlogPostResource;
use Rupadana\ApiService\ApiService;

class BlogPostApiService extends ApiService
{
    protected static ?string $resource = BlogPostResource::class;

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

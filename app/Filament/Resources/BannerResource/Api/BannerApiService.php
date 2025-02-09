<?php

namespace App\Filament\Resources\BannerResource\Api;

use App\Filament\Resources\BannerResource;
use Rupadana\ApiService\ApiService;

class BannerApiService extends ApiService
{
    protected static ?string $resource = BannerResource::class;

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

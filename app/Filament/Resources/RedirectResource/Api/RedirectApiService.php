<?php

namespace App\Filament\Resources\RedirectResource\Api;

use App\Filament\Resources\RedirectResource;
use Rupadana\ApiService\ApiService;

class RedirectApiService extends ApiService
{
    protected static ?string $resource = RedirectResource::class;

    public static function handlers(): array
    {
        return [
            Handlers\PaginationHandler::class,
        ];

    }
}

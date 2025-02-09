<?php

namespace App\Filament\Resources\ContactResource\Api;

use App\Filament\Resources\ContactResource;
use Rupadana\ApiService\ApiService;

class ContactApiService extends ApiService
{
    protected static ?string $resource = ContactResource::class;

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

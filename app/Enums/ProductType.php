<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasColor, HasIcon, HasLabel
{
    case Product = 'product';
    case Digital = 'digital';
    case Service = 'service';

    public static function options(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Digital => 'Digital',
            self::Service => 'Service'
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Product => 'green',
            self::Digital => 'cyan',
            self::Service => 'violet'
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Product => 'solar-box-line-duotone',
            self::Digital => 'solar-tv-line-duotone',
            self::Service => 'solar-server-square-line-duotone'
        };
    }
}

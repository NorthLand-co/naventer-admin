<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CategoryAttributeType: string implements HasColor, HasIcon, HasLabel
{
    case Number = 'number';
    case Text = 'text';
    case Boolean = 'boolean';
    case Range = 'range';
    case Dropdown = 'dropdown';
    case MultiSelect = 'multi-select';

    public static function options(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Number => 'Number',
            self::Text => 'Text',
            self::Boolean => 'Boolean',
            self::Range => 'Range',
            self::Dropdown => 'Dropdown',
            self::MultiSelect => 'Multi Select',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Number => 'gray',
            self::Text => 'gray',
            self::Boolean => 'gray',
            self::Range => 'gray',
            self::Dropdown => 'gray',
            self::MultiSelect => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Number => 'solar-4k-linear',
            self::Text => 'solar-text-bold',
            self::Boolean => 'solar-text-bold-outline',
            self::Range => 'solar-graph-broken',
            self::Dropdown => 'solar-alt-arrow-down-line-duotone',
            self::MultiSelect => 'solar-alt-arrow-down-line-duotone',
        };
    }
}

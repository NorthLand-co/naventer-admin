<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Status: int implements HasColor, HasIcon, HasLabel
{
    case Draft = 0;
    case Archived = 1;
    case Rejected = 2;
    case OnHold = 3;
    case Reviewing = 4;
    case Published = 5;

    public static function options(): array
    {
        return array_map(fn ($case) => trans('status.'.camelToSnakeCase($case->name)), self::cases());
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Archived => 'Archived',
            self::Rejected => 'Rejected',
            self::OnHold => 'On Hold',
            self::Reviewing => 'Reviewing',
            self::Published => 'Published',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Archived => 'danger',
            self::Rejected => 'danger',
            self::OnHold => 'warning',
            self::Reviewing => 'warning',
            self::Published => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'solar-notes-line-duotone',
            self::Archived => 'solar-archive-line-duotone',
            self::Rejected => 'solar-notification-lines-remove-line-duotone',
            self::OnHold => 'solar-pause-circle-line-duotone',
            self::Reviewing => 'solar-pulse-line-duotone',
            self::Published => 'solar-checklist-minimalistic-line-duotone',
        };
    }
}

<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Socials: string implements HasColor, HasIcon, HasLabel
{
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case WhatsApp = 'whatsapp';
    case Telegram = 'telegram';
    case Linkedin = 'linkedin';
    case Youtube = 'youtube';
    case X = 'x';
    case Aparat = 'aparat';

    public static function options(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Instagram => 'Instagram',
            self::WhatsApp => 'WhatsApp',
            self::Telegram => 'Telegram',
            self::Linkedin => 'Linkedin',
            self::Youtube => 'Youtube',
            self::X => 'X',
            self::Aparat => 'Aparat',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Facebook => 'blue',
            self::Instagram => 'rose',
            self::WhatsApp => 'lime',
            self::Telegram => 'cyan',
            self::Linkedin => 'blue',
            self::Youtube => 'red',
            self::X => 'slate',
            self::Aparat => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
        };
    }
}

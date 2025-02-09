<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BlogPostType: string implements HasColor, HasIcon, HasLabel
{
    case Article = 'article';
    case Video = 'video';
    case Gallery = 'gallery';
    case News = 'news';

    public static function options(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Article => 'Article',
            self::Video => 'Video',
            self::Gallery => 'Gallery',
            self::News => 'News',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Article => 'green',
            self::Video => 'orange',
            self::Gallery => 'cyan',
            self::News => 'rose',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Article => 'solar-pen-line-duotone',
            self::Video => 'solar-videocamera-record-line-duotone',
            self::Gallery => 'solar-gallery-wide-line-duotone',
            self::News => 'solar-tv-line-duotone',
        };
    }
}

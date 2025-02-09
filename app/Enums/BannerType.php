<?php

namespace App\Enums;

enum BannerType: string
{
    case home_page = 'homePage';
    case blog_index = 'blogIndex';
    case gallery = 'gallery';

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = trans('types.'.camelToSnakeCase($case->name));
        }

        return $options;
    }
}

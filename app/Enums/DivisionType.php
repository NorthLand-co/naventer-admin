<?php

namespace App\Enums;

enum DivisionType: int
{
    case Country = 0;
    case State = 1;
    case County = 2;
    case District = 3;
    case RuralDistrict = 4;
    case City = 5;
    case Village = 6;

    public function label(): string
    {
        return match ($this) {
            self::Country => 'Country',
            self::State => 'State',
            self::County => 'County',
            self::District => 'District',
            self::RuralDistrict => 'Rural District',
            self::City => 'City',
            self::Village => 'Village',
        };
    }
}

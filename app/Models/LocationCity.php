<?php

namespace App\Models;

use App\Enums\DivisionType;
use App\Models\Scopes\DivisionTypeScope;

class LocationCity extends Location
{
    protected $table = 'locations';

    protected static function booted()
    {
        static::addGlobalScope(new DivisionTypeScope(DivisionType::County->value));
    }
}

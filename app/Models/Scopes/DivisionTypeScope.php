<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DivisionTypeScope implements Scope
{
    protected int $divisionType;

    public function __construct(int $divisionType)
    {
        $this->divisionType = $divisionType;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('division_type', $this->divisionType);
    }
}

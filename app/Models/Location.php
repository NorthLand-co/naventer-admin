<?php

namespace App\Models;

use App\Enums\DivisionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'division_type', 'parent_country_division_id'];

    // Cast division_type to enum
    protected $casts = [
        'division_type' => DivisionType::class,
    ];

    /**
     * Get the parent location (e.g., parent region, country, etc.)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_country_division_id');
    }

    /**
     * Get the child locations (e.g., provinces, counties, etc.)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_country_division_id');
    }

    public function findHighestStateAncestor(): ?self
    {
        $current = $this;

        while ($current->parent) {
            if ($current->parent->division_type == DivisionType::State) {
                return $current->parent;
            }
            $current = $current->parent;
        }

        return null;
    }
}

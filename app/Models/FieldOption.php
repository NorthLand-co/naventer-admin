<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['model', 'field', 'value'];

    /**
     * Scope to filter options for a specific model and field.
     */
    public function scopeForField($query, string $model, string $field)
    {
        return $query->where('model', $model)->where('field', $field);
    }

    public function relatedModel()
    {
        return $this->belongsTo($this->model, 'id', 'id');
    }
}

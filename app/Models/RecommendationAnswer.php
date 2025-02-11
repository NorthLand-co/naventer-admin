<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RecommendationAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['recommendation_question_id', 'title', 'description', 'icon', 'order'];

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(RecommendationQuestion::class, 'recommendation_question_id');
    }

    /**
     * Get the products associated with this answer.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recommendation_product_answers');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'icon', 'order', 'weight'];

    /**
     * Get the answers associated with the question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(RecommendationQuestion::class, 'recommendation_question_id');
    }
}

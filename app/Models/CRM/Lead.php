<?php

namespace App\Models\CRM;

use App\Models\Contact;
use App\Models\FieldOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    protected $fillable = ['title', 'notes', 'source', 'status', 'contact_id', 'user_id'];

    public function options(): HasMany
    {
        return $this->hasMany(FieldOption::class, 'model', 'field')
            ->where('field', 'source');
    }

    public function calls(): MorphMany
    {
        return $this->morphMany(Call::class, 'callable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function statusOption(): BelongsTo
    {
        return $this->belongsTo(FieldOption::class, 'status', 'id');
    }

    public function sourceOption(): BelongsTo
    {
        return $this->belongsTo(FieldOption::class, 'source', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'bio',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarAttribute($value)
    {
        // Ensure the avatar attribute is set and not empty
        if ($value) {
            return env('AWS_URL').'/'.ltrim($value, '/');
        }

        // Return a default avatar URL if the avatar is not set
        return env('DEFAULT_AVATAR_URL');
    }
}

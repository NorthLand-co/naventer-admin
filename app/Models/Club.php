<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Club extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = ['name', 'email', 'phone'];

    /**
     * Get the user that owns the Club
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routeNotificationForKavenegar($driver, $notification = null)
    {
        return $this->phone;
    }
}

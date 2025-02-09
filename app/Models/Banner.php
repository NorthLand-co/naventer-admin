<?php

namespace App\Models;

use App\Enums\BannerType;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = ['type', 'name', 'slug', 'url', 'alt', 'description', 'styles', 'target', 'class', 'order', 'started_at', 'ended_at', 'status'];

    protected $casts = [
        'status' => Status::class,
        'type' => BannerType::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public static array $allowedFilters = ['type'];

    public static array $allowedSorts = ['order', 'created_at'];
}

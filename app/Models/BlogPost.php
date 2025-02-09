<?php

namespace App\Models;

use App\Enums\BlogPostType;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogPost extends Model implements HasMedia
{
    use HasFactory;
    use HasFilamentComments;
    use InteractsWithMedia;
    use Searchable;

    protected $fillable = ['author_id', 'title', 'content', 'excerpt', 'slug', 'status', 'is_featured', 'allow_comments', 'post_type'];

    protected $dates = ['published_at'];

    public static array $allowedIncludes = ['author.profile', 'seo', 'faqs', 'comments'];

    public static array $allowedFilters = ['title', 'post_type'];

    protected $casts = [
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'status' => Status::class,
        'post_type' => BlogPostType::class,
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (Auth::check()) {
                $post->author_id = Auth::id();
            }
        });
    }

    public function setStatusAttribute($value)
    {
        if ($value == 5 && $this->status != 5) {
            $this->attributes['published_at'] = now();
        }
        $this->attributes['status'] = $value;
    }

    // Relations
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function faqs()
    {
        return $this->morphMany(FAQ::class, 'faqable');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('thumb')
            ->useDisk('s3');
    }

    public function thumb(): ?Media
    {
        return $this->getFirstMedia('thumb');
    }

    public function gallery(): ?Media
    {
        return $this->getFirstMedia('gallery');
    }

    // Getters
    public function getThumbAttribute()
    {
        return $this->thumb();
    }

    public function getGalleryAttribute()
    {
        return $this->thumb();
    }
}

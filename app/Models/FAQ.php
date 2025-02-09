<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    protected $table = 'faqs';

    protected static ?string $slug = 'faqs';

    protected $fillable = ['question', 'answer', 'order'];

    public function faqable()
    {
        return $this->morphTo();
    }
}

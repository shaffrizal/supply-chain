<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';
    protected $guarded = [];
    protected $casts = ['published_at' => 'datetime'];
}

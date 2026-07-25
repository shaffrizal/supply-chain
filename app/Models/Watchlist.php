<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $guarded = [];
    public function country() { return $this->belongsTo(Country::class); }
    public function user() { return $this->belongsTo(User::class); }
}

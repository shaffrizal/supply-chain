<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $guarded = [];
    protected $casts = ['snapshot_date'=>'date','weather_risk'=>'float','inflation_risk'=>'float','news_risk'=>'float','currency_risk'=>'float','total_score'=>'float'];
    public function country() { return $this->belongsTo(Country::class); }
}

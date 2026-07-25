<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [

        'country_code',

        'country_name',

        'flag',

        'currency',

        'exchange_rate',

        'gdp',

        'population',

        'region',

        'capital',

        'risk_index',

        'risk_level',
        'latitude',
        'longitude'

    ];

    /**
     * One Country -> Many Ports
     */
    public function ports(): HasMany
    {
        return $this->hasMany(
            Port::class,
            'country_code',
            'country_code'
        );
    }

    public function riskScores(): HasMany { return $this->hasMany(RiskScore::class); }
    public function watchlists(): HasMany { return $this->hasMany(Watchlist::class); }
}

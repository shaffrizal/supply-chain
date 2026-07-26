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
        'languages',

        'exchange_rate',

        'gdp',
        'inflation_rate',
        'gdp_growth',
        'trade_percentage',
        'exports_value',
        'imports_value',
        'economic_data_year',
        'population_data_year',
        'inflation_data_year',
        'growth_data_year',
        'trade_data_year',
        'exports_data_year',
        'imports_data_year',
        'world_bank_synced_at',

        'population',

        'region',

        'capital',

        'risk_index',

        'risk_level',
        'latitude',
        'longitude'

    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'inflation_rate' => 'float',
            'gdp_growth' => 'float',
            'trade_percentage' => 'float',
            'exports_value' => 'float',
            'imports_value' => 'float',
            'world_bank_synced_at' => 'datetime',
        ];
    }

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

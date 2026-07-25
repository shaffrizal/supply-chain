<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    protected $fillable = [

        'port_code',

        'port_name',

        'country_code',

        'country',

        'city',

        'latitude',

        'longitude',

        'port_type',

        'annual_capacity',

        'status',

        'risk_level'
        ,'risk_index'

    ];

    /**
     * Port belongs to Country
     */
    public function countryData(): BelongsTo
    {
        return $this->belongsTo(
            Country::class,
            'country_code',
            'country_code'
        );
    }

    public function routesFrom()
{
    return $this->hasMany(
        ShippingRoute::class,
        'origin_port_id'
    );
}

public function routesTo()
{
    return $this->hasMany(
        ShippingRoute::class,
        'destination_port_id'
    );
}

}

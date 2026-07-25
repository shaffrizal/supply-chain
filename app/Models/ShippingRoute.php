<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRoute extends Model
{
    protected $fillable = [

        'origin_port_id',

        'destination_port_id',

        'distance_km',

        'estimated_days',

        'route_status'

    ];

    public function origin()
    {
        return $this->belongsTo(
            Port::class,
            'origin_port_id'
        );
    }

    public function destination()
    {
        return $this->belongsTo(
            Port::class,
            'destination_port_id'
        );
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;

class MapController extends Controller
{
    public function index()
    {
        $countries = Country::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('country_name')
            ->get(['id', 'country_code', 'country_name', 'capital', 'region', 'population', 'latitude', 'longitude', 'risk_index'])
            ->map(function (Country $country) {
                $score = max(0, min(100, (float) $country->risk_index));
                $level = $score >= 70 ? 'High' : ($score >= 40 ? 'Medium' : 'Low');

                return [
                    'id' => $country->id,
                    'code' => strtoupper($country->country_code),
                    'name' => $country->country_name,
                    'capital' => $country->capital ?: '—',
                    'region' => $country->region ?: 'Global',
                    'population' => (int) $country->population,
                    'latitude' => (float) $country->latitude,
                    'longitude' => (float) $country->longitude,
                    'score' => round($score, 1),
                    'level' => $level,
                    'url' => route('countries.show', $country),
                ];
            });

        $riskCounts = [
            'High' => $countries->where('level', 'High')->count(),
            'Medium' => $countries->where('level', 'Medium')->count(),
            'Low' => $countries->where('level', 'Low')->count(),
        ];

        return view('map.index', [
            'mapCountries' => $countries->values(),
            'mapPorts' => Port::query()->whereNotNull('latitude')->whereNotNull('longitude')
                ->get(['id', 'port_name', 'country', 'city', 'latitude', 'longitude', 'risk_index'])
                ->map(fn (Port $port) => [
                    'id' => $port->id,
                    'name' => $port->port_name,
                    'country' => $port->country ?: '—',
                    'city' => $port->city ?: '—',
                    'latitude' => (float) $port->latitude,
                    'longitude' => (float) $port->longitude,
                    'score' => (int) $port->risk_index,
                    'level' => $port->risk_index >= 70 ? 'High' : ($port->risk_index >= 40 ? 'Medium' : 'Low'),
                    'url' => route('ports.show', $port),
                ])->values(),
            'totalCountries' => $countries->count(),
            'totalPorts' => Port::count(),
            'averageRisk' => round((float) $countries->avg('score'), 1),
            'riskCounts' => $riskCounts,
            'regionsCount' => $countries->pluck('region')->filter()->unique()->count(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCountry = Country::count();

        $asia = Country::where('region', 'Asia')->count();
        $europe = Country::where('region', 'Europe')->count();
        $africa = Country::where('region', 'Africa')->count();
        $america = Country::where('region', 'Americas')->count();
        $oceania = Country::where('region', 'Oceania')->count();

        $countries = Country::latest()->take(5)->get();

        /*
        |--------------------------------------------------------------------------
        | Open Meteo
        |--------------------------------------------------------------------------
        */

        try {

            $response = Http::timeout(10)->get(
                'https://api.open-meteo.com/v1/forecast',
                [
                    'latitude' => -6.2088,
                    'longitude' => 106.8456,
                    'current' =>
                        'temperature_2m,relative_humidity_2m,wind_speed_10m'
                ]
            );

            $weather = $response->successful()
                ? $response->json()
                : [];

        } catch (\Exception $e) {

            $weather = [];

        }

        /*
        |--------------------------------------------------------------------------
        | Exchange Rate
        |--------------------------------------------------------------------------
        */

        try {

            $exchange = Http::timeout(10)
                ->get('https://open.er-api.com/v6/latest/USD')
                ->json();

        } catch (\Exception $e) {

            $exchange = [];

        }

        return view(
            'dashboard.dashboard',
            compact(
                'totalCountry',
                'asia',
                'europe',
                'africa',
                'america',
                'oceania',
                'countries',
                'weather',
                'exchange'
            )
        );
    }
}
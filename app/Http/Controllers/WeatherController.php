<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function show($city)
    {
        $apiKey = env('OPENWEATHER_API_KEY');

        $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'id'
        ]);

        if ($response->failed()) {
            return back()->with('error', 'Data cuaca tidak ditemukan.');
        }

        $weather = $response->json();

        return view('weather.show', compact('weather'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;

class WeatherController extends Controller
{
    public function index(WeatherService $weatherService)
    {
        $countries = $this->countriesWithCoordinates();
        $weatherData = collect($weatherService->globalConditions($countries))->map(function (array $point) {
            $current = [
                'temperature_2m' => $point['temperature'],
                'apparent_temperature' => $point['apparent_temperature'],
                'relative_humidity_2m' => $point['humidity'],
                'precipitation' => $point['precipitation'],
                'surface_pressure' => $point['pressure'],
                'wind_speed_10m' => $point['wind'],
                'wind_gusts_10m' => $point['gust'],
                'weather_code' => $point['weather_code'],
            ];
            $risk = $this->weatherRisk($current);

            return [
                'name' => $point['name'],
                'country' => $point['capital'] ?: 'National observation',
                'code' => $point['code'],
                'flag' => $point['flag'],
                'current' => $current,
                'risk_score' => $risk,
                'risk_level' => $risk >= 70 ? 'High' : ($risk >= 40 ? 'Medium' : 'Low'),
            ];
        });

        return view('weather.index', [
            'weatherData' => $weatherData,
            'averageTemperature' => round((float) $weatherData->avg(fn ($item) => $item['current']['temperature_2m'] ?? 0), 1),
            'highRiskCities' => $weatherData->where('risk_level', 'High')->count(),
            'rainCities' => $weatherData->filter(fn ($item) => (float) ($item['current']['precipitation'] ?? 0) > 0)->count(),
            'maxWind' => round((float) $weatherData->max(fn ($item) => $item['current']['wind_speed_10m'] ?? 0), 1),
        ]);
    }

    public function map(WeatherService $weatherService)
    {
        $points = collect($weatherService->globalConditions($this->countriesWithCoordinates()));

        return view('weather.map', [
            'weatherPoints' => $points,
            'mapCountries' => $points->unique('code')->sortBy('name')->values(),
            'rainCount' => $points->where('rain', true)->count(),
            'stormCount' => $points->where('storm', true)->count(),
            'windCount' => $points->where('strong_wind', true)->count(),
        ]);
    }

    public function show(string $city)
    {
        return redirect()->route('weather.index', ['focus' => $city]);
    }

    private function countriesWithCoordinates()
    {
        return Country::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('country_name')
            ->get(['country_code', 'country_name', 'capital', 'flag', 'latitude', 'longitude', 'updated_at']);
    }

    private function weatherRisk(array $current): int
    {
        $wind = min(45, (float) ($current['wind_speed_10m'] ?? 0) * 1.5);
        $rain = min(35, (float) ($current['precipitation'] ?? 0) * 8);
        $storm = (int) ($current['weather_code'] ?? 0) >= 80 ? 20 : 0;

        return (int) round(min(100, $wind + $rain + $storm));
    }
}

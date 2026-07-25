<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getWeather($latitude, $longitude)
    {
        $url = "https://api.open-meteo.com/v1/forecast";

        $response = Http::timeout(10)->get($url, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,surface_pressure,wind_speed_10m,wind_gusts_10m,weather_code'
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        return $data['current'] ?? null;
    }

    public function globalConditions(Collection $countries): array
    {
        $locations = $countries
            ->filter(fn ($country) => $country->latitude !== null && $country->longitude !== null)
            ->values();

        $signature = md5($locations->pluck('updated_at')->join('|').$locations->count());

        return Cache::remember("weather.global-countries.v2.$signature", now()->addMinutes(15), function () use ($locations) {
            $points = [];

            foreach ($locations->chunk(40) as $chunk) {
                try {
                    $response = Http::connectTimeout(3)->timeout(15)->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $chunk->pluck('latitude')->implode(','),
                        'longitude' => $chunk->pluck('longitude')->implode(','),
                        'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,rain,surface_pressure,weather_code,wind_speed_10m,wind_gusts_10m',
                        'timezone' => 'auto',
                    ]);

                    if (! $response->successful()) {
                        continue;
                    }

                    $payload = $response->json();
                    $responses = isset($payload['current']) ? [$payload] : (is_array($payload) ? array_values($payload) : []);

                    foreach ($chunk->values() as $index => $country) {
                        $current = $responses[$index]['current'] ?? null;
                        if (! is_array($current) || ! isset($current['temperature_2m'])) {
                            continue;
                        }

                        $code = (int) ($current['weather_code'] ?? -1);
                        $precipitation = (float) ($current['precipitation'] ?? $current['rain'] ?? 0);
                        $wind = (float) ($current['wind_speed_10m'] ?? 0);
                        $gust = (float) ($current['wind_gusts_10m'] ?? 0);

                        $points[] = [
                            'code' => $country->country_code,
                            'name' => $country->country_name,
                            'capital' => $country->capital,
                            'flag' => $country->flag,
                            'lat' => (float) $country->latitude,
                            'lon' => (float) $country->longitude,
                            'temperature' => (float) $current['temperature_2m'],
                            'apparent_temperature' => isset($current['apparent_temperature']) ? (float) $current['apparent_temperature'] : null,
                            'humidity' => isset($current['relative_humidity_2m']) ? (float) $current['relative_humidity_2m'] : null,
                            'precipitation' => $precipitation,
                            'pressure' => isset($current['surface_pressure']) ? (float) $current['surface_pressure'] : null,
                            'wind' => $wind,
                            'gust' => $gust,
                            'weather_code' => $code,
                            'rain' => $precipitation > 0 || ($code >= 51 && $code <= 82),
                            'storm' => $code >= 95,
                            'strong_wind' => $wind >= 40 || $gust >= 60,
                            'observed_at' => $current['time'] ?? null,
                        ];
                    }
                } catch (\Throwable) {
                    continue;
                }
            }

            return $points;
        });
    }
}

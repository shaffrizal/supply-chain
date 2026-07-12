<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Support\Facades\Http;

class RiskScoreController extends Controller
{
    public function index()
    {
        set_time_limit(0);

        $countries = Country::all();

        $results = [];

        // Ambil kurs USD sekali saja
        $exchangeResponse = Http::get('https://open.er-api.com/v6/latest/USD');

        $rates = [];

        if ($exchangeResponse->successful()) {
            $exchangeJson = $exchangeResponse->json();
            $rates = $exchangeJson['rates'] ?? [];
        }

        foreach ($countries as $country) {

            /*
            |--------------------------------------------------------------------------
            | GDP
            |--------------------------------------------------------------------------
            */

            $gdp = null;

            $gdpResponse = Http::get(
                "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/NY.GDP.MKTP.CD?format=json"
            );

            $gdpJson = $gdpResponse->json();

            if (isset($gdpJson[1])) {

                foreach ($gdpJson[1] as $item) {

                    if (!is_null($item['value'])) {

                        $gdp = $item['value'];

                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Population
            |--------------------------------------------------------------------------
            */

            $population = null;

            $populationResponse = Http::get(
                "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/SP.POP.TOTL?format=json"
            );

            $populationJson = $populationResponse->json();

            if (isset($populationJson[1])) {

                foreach ($populationJson[1] as $item) {

                    if (!is_null($item['value'])) {

                        $population = $item['value'];

                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Inflation
            |--------------------------------------------------------------------------
            */

            $inflation = null;

            $inflationResponse = Http::get(
                "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/FP.CPI.TOTL.ZG?format=json"
            );

            $inflationJson = $inflationResponse->json();

            if (isset($inflationJson[1])) {

                foreach ($inflationJson[1] as $item) {

                    if (!is_null($item['value'])) {

                        $inflation = round($item['value'], 2);

                        break;
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Exchange Rate
            |--------------------------------------------------------------------------
            */

            $exchangeRate = null;

            if (
                !empty($country->currency) &&
                isset($rates[$country->currency])
            ) {

                $exchangeRate = $rates[$country->currency];

            }

            /*
            |--------------------------------------------------------------------------
            | Weather
            |--------------------------------------------------------------------------
            */

            $weather = null;

            if (!empty($country->capital)) {

            $weatherResponse = Http::get(
            'https://api.openweathermap.org/data/2.5/weather',
            [
            'q' => $country->capital,
            'appid' => env('OPENWEATHER_API_KEY'),
            'units' => 'metric'
            ]
            );

            if ($weatherResponse->successful()) {

        $weatherJson = $weatherResponse->json();

        $weather = $weatherJson['weather'][0]['main'] ?? null;

        }
        
        /*
|--------------------------------------------------------------------------
| Hitung Risk Score
|--------------------------------------------------------------------------
*/

// GDP
$gdpScore = 40;

if ($gdp >= 1000000000000) {
    $gdpScore = 100;
} elseif ($gdp >= 500000000000) {
    $gdpScore = 80;
} elseif ($gdp >= 100000000000) {
    $gdpScore = 60;
}

// Inflation
$inflationScore = 40;

if ($inflation !== null) {

    if ($inflation < 3) {
        $inflationScore = 100;
    } elseif ($inflation < 6) {
        $inflationScore = 80;
    } elseif ($inflation < 10) {
        $inflationScore = 60;
    }

}

// Population
$populationScore = 40;

if ($population >= 100000000) {
    $populationScore = 100;
} elseif ($population >= 50000000) {
    $populationScore = 80;
} elseif ($population >= 10000000) {
    $populationScore = 60;
}

// Exchange Rate
$exchangeScore = 80;

if ($exchangeRate !== null) {

    if ($exchangeRate < 2) {
        $exchangeScore = 100;
    } elseif ($exchangeRate < 20) {
        $exchangeScore = 80;
    } else {
        $exchangeScore = 60;
    }

}

// Weather
$weatherScore = 80;

switch ($weather) {

    case 'Clear':
        $weatherScore = 100;
        break;

    case 'Clouds':
        $weatherScore = 80;
        break;

    case 'Drizzle':
        $weatherScore = 70;
        break;

    case 'Rain':
        $weatherScore = 60;
        break;

    case 'Thunderstorm':
        $weatherScore = 40;
        break;
}

// Hitung skor akhir
$riskScore =
    ($gdpScore * 0.30) +
    ($inflationScore * 0.25) +
    ($populationScore * 0.15) +
    ($exchangeScore * 0.15) +
    ($weatherScore * 0.15);

// Tentukan kategori
if ($riskScore >= 80) {
    $risk = 'Low';
} elseif ($riskScore >= 60) {
    $risk = 'Medium';
} else {
    $risk = 'High';
}

        /*
|--------------------------------------------------------------------------
| Hitung Risk Score
|--------------------------------------------------------------------------
*/

// GDP
$gdpScore = 40;

if ($gdp >= 1000000000000) {
    $gdpScore = 100;
} elseif ($gdp >= 500000000000) {
    $gdpScore = 80;
} elseif ($gdp >= 100000000000) {
    $gdpScore = 60;
}

// Inflation
$inflationScore = 40;

if ($inflation !== null) {

    if ($inflation < 3) {
        $inflationScore = 100;
    } elseif ($inflation < 6) {
        $inflationScore = 80;
    } elseif ($inflation < 10) {
        $inflationScore = 60;
    }

}

// Population
$populationScore = 40;

if ($population >= 100000000) {
    $populationScore = 100;
} elseif ($population >= 50000000) {
    $populationScore = 80;
} elseif ($population >= 10000000) {
    $populationScore = 60;
}

// Exchange Rate
$exchangeScore = 80;

if ($exchangeRate !== null) {

    if ($exchangeRate < 2) {
        $exchangeScore = 100;
    } elseif ($exchangeRate < 20) {
        $exchangeScore = 80;
    } else {
        $exchangeScore = 60;
    }

}

// Weather
$weatherScore = 80;

switch ($weather) {

    case 'Clear':
        $weatherScore = 100;
        break;

    case 'Clouds':
        $weatherScore = 80;
        break;

    case 'Drizzle':
        $weatherScore = 70;
        break;

    case 'Rain':
        $weatherScore = 60;
        break;

    case 'Thunderstorm':
        $weatherScore = 40;
        break;
}

// Hitung skor akhir
$riskScore =
    ($gdpScore * 0.30) +
    ($inflationScore * 0.25) +
    ($populationScore * 0.15) +
    ($exchangeScore * 0.15) +
    ($weatherScore * 0.15);

// Tentukan kategori
if ($riskScore >= 80) {
    $risk = 'Low';
} elseif ($riskScore >= 60) {
    $risk = 'Medium';
} else {
    $risk = 'High';
}

        }      

            /*
            |--------------------------------------------------------------------------
            | Simpan ke array
            |--------------------------------------------------------------------------
            */

            $results[] = [

                'country' => $country,

                'gdp' => $gdp,

                'population' => $population,

                'inflation' => $inflation,

                'exchange_rate' => $exchangeRate,

                'weather' => $weather,

                'risk_score' => round($riskScore),

                'risk' => $risk,

            ];
        }

        return view('risk.index', compact('results'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class WorldBankController extends Controller
{
    public function show($code)
    {
        // ========================
        // GDP
        // ========================
        $gdpResponse = Http::get("https://api.worldbank.org/v2/country/$code/indicator/NY.GDP.MKTP.CD?format=json");
        $gdpJson = $gdpResponse->json();

        $gdp = null;

        if (isset($gdpJson[1])) {
            foreach ($gdpJson[1] as $item) {
                if (!is_null($item['value'])) {
                    $gdp = [
                        'country' => $item['country']['value'],
                        'year' => $item['date'],
                        'value' => $item['value'],
                    ];
                    break;
                }
            }
        }

        // ========================
        // Populasi
        // ========================
        $populationResponse = Http::get("https://api.worldbank.org/v2/country/$code/indicator/SP.POP.TOTL?format=json");
        $populationJson = $populationResponse->json();

        $population = null;

        if (isset($populationJson[1])) {
            foreach ($populationJson[1] as $item) {
                if (!is_null($item['value'])) {
                    $population = [
                        'year' => $item['date'],
                        'value' => $item['value'],
                    ];
                    break;
                }
            }
        }

        // ========================
        // Inflasi
        // ========================
        $inflationResponse = Http::get("https://api.worldbank.org/v2/country/$code/indicator/FP.CPI.TOTL.ZG?format=json");
        $inflationJson = $inflationResponse->json();

        $inflation = null;

        if (isset($inflationJson[1])) {
            foreach ($inflationJson[1] as $item) {
                if (!is_null($item['value'])) {
                    $inflation = [
                        'year' => $item['date'],
                        'value' => round($item['value'], 2),
                    ];
                    break;
                }
            }
        }

        return view('worldbank.show', compact(
            'gdp',
            'population',
            'inflation'
        ));
    }
}
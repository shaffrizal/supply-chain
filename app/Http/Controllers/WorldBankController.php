<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WorldBankController extends Controller
{
    private const INDICATORS = [
        'gdp' => 'NY.GDP.MKTP.CD',
        'population' => 'SP.POP.TOTL',
        'inflation' => 'FP.CPI.TOTL.ZG',
        'growth' => 'NY.GDP.MKTP.KD.ZG',
        'trade' => 'NE.TRD.GNFS.ZS',
    ];

    public function index(Request $request)
    {
        $countries = Country::orderBy('country_name')->get();
        $trendCountries = $countries->mapWithKeys(function (Country $country): array {
            $code = strtoupper((string) $country->country_code);

            return [$code => [
                'name' => $country->country_name,
                'flag' => $country->flag,
            ]];
        });
        $economyData = $countries->map(function (Country $country): array {
            return [
                'code' => strtoupper((string) $country->country_code),
                'alpha2' => strtolower((string) $country->country_code),
                'name' => $country->country_name,
                'flag' => $country->flag,
                'region' => $country->region,
                'gdp' => $country->gdp !== null ? (float) $country->gdp : null,
                'gdp_year' => null,
                'population' => $country->population !== null ? (float) $country->population : null,
                'population_year' => null,
                'inflation' => null,
                'inflation_year' => null,
                'growth' => null,
                'growth_year' => null,
                'trade' => null,
                'trade_year' => null,
            ];
        });

        $requestedCode = strtoupper((string) $request->query('country', ''));
        $trendCode = $trendCountries->has($requestedCode)
            ? $requestedCode
            : (string) ($trendCountries->keys()->first() ?? '');
        $trendCountry = $trendCode !== ''
            ? ['code' => $trendCode, ...$trendCountries->get($trendCode)]
            : ['code' => '', 'name' => 'No country selected', 'flag' => null];
        $gdpTrend = $trendCode !== ''
            ? $this->history($trendCode, self::INDICATORS['gdp'])
            : [];
        $inflationTrend = $trendCode !== ''
            ? $this->history($trendCode, self::INDICATORS['inflation'])
            : [];

        return view('economy.index', [
            'economyData' => $economyData,
            'totalGdp' => $economyData->sum('gdp'),
            'totalPopulation' => $economyData->sum('population'),
            'averageInflation' => round((float) $economyData->whereNotNull('inflation')->avg('inflation'), 2),
            'averageGrowth' => round((float) $economyData->whereNotNull('growth')->avg('growth'), 2),
            'largestEconomy' => $economyData->whereNotNull('gdp')->sortByDesc('gdp')->first(),
            'trendCode' => $trendCode,
            'trendCountry' => $trendCountry,
            'trendCountries' => $trendCountries,
            'gdpTrend' => $gdpTrend,
            'inflationTrend' => $inflationTrend,
        ]);
    }

    public function show(string $country)
    {
        return redirect()->route(
            'economy.index',
            [
                'country' => $country
            ]
        );
    }

    private function history(
        string $country,
        string $indicator
    ): array {
        return collect($this->indicatorData($country, $indicator))
                ->filter(fn (array $row): bool => ($row['value'] ?? null) !== null)
                ->sortBy('date')
                ->map(fn (array $row): array => [
                    'year' => $row['date'] ?? null,
                    'value' => $row['value'],
                ])
                ->values()
                ->all();
    }

    private function indicatorData(string $country, string $indicator): array
    {
        try {
            return Cache::remember(
                "world-bank.indicator.{$country}.{$indicator}",
                now()->addHours(6),
                function () use ($country, $indicator): array {
                    $response = Http::connectTimeout(3)
                        ->timeout(8)
                        ->get("https://api.worldbank.org/v2/country/{$country}/indicator/{$indicator}", [
                            'format' => 'json',
                            'per_page' => 30,
                        ]);

                    if (!$response->successful()) {
                        return [];
                    }

                    $rows = $response->json()[1] ?? [];

                    return is_array($rows) ? $rows : [];
                }
            );
        } catch (\Throwable) {
            return [];
        }
    }
}

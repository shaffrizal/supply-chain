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
        'exports' => 'NE.EXP.GNFS.CD',
        'imports' => 'NE.IMP.GNFS.CD',
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
                'gdp_year' => $country->economic_data_year,
                'population' => $country->population !== null ? (float) $country->population : null,
                'population_year' => null,
                'inflation' => $country->inflation_rate,
                'inflation_year' => $country->economic_data_year,
                'exports' => $country->exports_value,
                'exports_year' => $country->economic_data_year,
                'imports' => $country->imports_value,
                'imports_year' => $country->economic_data_year,
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
        $exportTrend = $trendCode !== ''
            ? $this->history($trendCode, self::INDICATORS['exports'])
            : [];
        $importTrend = $trendCode !== ''
            ? $this->history($trendCode, self::INDICATORS['imports'])
            : [];

        if ($trendCode !== '') {
            $latest = [
                'gdp' => collect($gdpTrend)->last(),
                'inflation' => collect($inflationTrend)->last(),
                'exports' => collect($exportTrend)->last(),
                'imports' => collect($importTrend)->last(),
            ];
            $selected = $countries->firstWhere('country_code', $trendCode);
            if ($selected) {
                $selected->update([
                    'gdp' => data_get($latest, 'gdp.value', $selected->gdp),
                    'inflation_rate' => data_get($latest, 'inflation.value', $selected->inflation_rate),
                    'exports_value' => data_get($latest, 'exports.value', $selected->exports_value),
                    'imports_value' => data_get($latest, 'imports.value', $selected->imports_value),
                    'economic_data_year' => data_get($latest, 'gdp.year', $selected->economic_data_year),
                ]);
                $economyData = $economyData->map(function (array $row) use ($selected): array {
                    if ($row['code'] !== $selected->country_code) return $row;
                    return [...$row,
                        'gdp' => $selected->gdp,
                        'inflation' => $selected->inflation_rate,
                        'exports' => $selected->exports_value,
                        'imports' => $selected->imports_value,
                        'gdp_year' => $selected->economic_data_year,
                    ];
                });
            }
        }

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
            'exportTrend' => $exportTrend,
            'importTrend' => $importTrend,
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

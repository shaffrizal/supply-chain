<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class WorldBankBatchSyncService
{
    private const INDICATORS = [
        'gdp' => ['code' => 'NY.GDP.MKTP.CD', 'column' => 'gdp', 'year' => 'economic_data_year'],
        'population' => ['code' => 'SP.POP.TOTL', 'column' => 'population', 'year' => 'population_data_year'],
        'inflation' => ['code' => 'FP.CPI.TOTL.ZG', 'column' => 'inflation_rate', 'year' => 'inflation_data_year'],
        'growth' => ['code' => 'NY.GDP.MKTP.KD.ZG', 'column' => 'gdp_growth', 'year' => 'growth_data_year'],
        'trade' => ['code' => 'NE.TRD.GNFS.ZS', 'column' => 'trade_percentage', 'year' => 'trade_data_year'],
        'exports' => ['code' => 'NE.EXP.GNFS.CD', 'column' => 'exports_value', 'year' => 'exports_data_year'],
        'imports' => ['code' => 'NE.IMP.GNFS.CD', 'column' => 'imports_value', 'year' => 'imports_data_year'],
    ];

    public function sync(bool $fresh = false, ?callable $progress = null): array
    {
        $iso3ToIso2 = $this->iso3ToIso2();
        $updates = [];
        $coverage = [];
        $errors = [];

        foreach (self::INDICATORS as $name => $indicator) {
            $coverage[$name] = 0;

            try {
                $rows = $this->latestObservations($indicator['code'], $fresh);
            } catch (Throwable $exception) {
                $errors[$name] = $exception->getMessage();

                if ($progress) {
                    $progress($name, 0);
                }

                continue;
            }

            foreach ($rows as $row) {
                $iso2 = $iso3ToIso2[strtoupper((string) ($row['countryiso3code'] ?? ''))] ?? null;
                if (! $iso2 || ($row['value'] ?? null) === null) {
                    continue;
                }

                $updates[$iso2][$indicator['column']] = $row['value'];
                $updates[$iso2][$indicator['year']] = is_numeric($row['date'] ?? null) ? (int) $row['date'] : null;
                $coverage[$name]++;
            }

            if ($progress) {
                $progress($name, $coverage[$name]);
            }
        }

        if ($updates === []) {
            throw new RuntimeException('World Bank did not return usable data for any indicator.');
        }

        $updatedCountries = 0;
        foreach (array_chunk($updates, 50, true) as $chunk) {
            foreach ($chunk as $iso2 => $values) {
                $updatedCountries += Country::where('country_code', $iso2)->update([
                    ...$values,
                    'world_bank_synced_at' => now(),
                ]);
            }
        }

        Cache::put('world-bank.sync.summary', [
            'countries' => $updatedCountries,
            'coverage' => $coverage,
            'errors' => $errors,
            'synced_at' => now()->toIso8601String(),
        ], now()->addDays(7));

        return [
            'countries' => $updatedCountries,
            'coverage' => $coverage,
            'errors' => $errors,
        ];
    }

    private function latestObservations(string $indicator, bool $fresh): array
    {
        $key = "world-bank.batch.latest.$indicator";
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addHours(6), function () use ($indicator): array {
            $response = Http::connectTimeout(5)->timeout(45)->retry(2, 500)
                ->get("https://api.worldbank.org/v2/country/all/indicator/$indicator", [
                    'format' => 'json',
                    'mrnev' => 1,
                    'per_page' => 500,
                    'source' => 2,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException("World Bank returned HTTP {$response->status()} for $indicator.");
            }

            $rows = $response->json()[1] ?? [];

            return is_array($rows) ? $rows : [];
        });
    }

    private function iso3ToIso2(): array
    {
        $countries = json_decode(
            file_get_contents(database_path('data/countries.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $mapping = [];
        foreach ($countries as $country) {
            if (isset($country['cca2'], $country['cca3'])) {
                $mapping[strtoupper($country['cca3'])] = strtoupper($country['cca2']);
            }
        }

        return $mapping;
    }
}

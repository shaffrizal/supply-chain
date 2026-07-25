<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use RuntimeException;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/countries.json');
        $countries = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        if (count($countries) !== 250) {
            throw new RuntimeException('Dataset countries.json harus berisi tepat 250 negara.');
        }

        foreach ($countries as $item) {
            $currency = array_key_first($item['currencies'] ?? []);
            $riskIndex = 10 + (abs(crc32($item['cca2'])) % 81);

            Country::updateOrCreate(
                ['country_code' => $item['cca2']],
                [
                    'country_name' => $item['name']['common'],
                    'flag' => $item['flag'] ?? null,
                    'currency' => $currency,
                    'languages' => array_values($item['languages'] ?? []),
                    'population' => $item['population'] ?? null,
                    'region' => $item['region'] ?: 'Other',
                    'capital' => $item['capital'][0] ?? null,
                    'latitude' => $item['latlng'][0] ?? null,
                    'longitude' => $item['latlng'][1] ?? null,
                    'risk_index' => $riskIndex,
                    'risk_level' => $riskIndex >= 70 ? 'High' : ($riskIndex >= 40 ? 'Medium' : 'Low'),
                ]
            );
        }

        $validCodes = array_column($countries, 'cca2');
        Country::whereNotIn('country_code', $validCodes)->delete();
    }
}

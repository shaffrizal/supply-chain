<?php

namespace App\Imports;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CountriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Country([

            'country_name' => trim($row['country_name'] ?? ''),

            'country_code' => strtoupper(trim($row['country_code'] ?? '')),

            'currency' => trim($row['currency'] ?? ''),

            'region' => trim($row['region'] ?? ''),

            'capital' => trim($row['capital'] ?? ''),

            'population' => $row['population'] ?? 0,

            'latitude' => $row['latitude'] ?? null,

            'longitude' => $row['longitude'] ?? null,

        ]);
    }
}
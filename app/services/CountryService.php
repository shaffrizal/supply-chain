<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    public function getCountry(string $country)
    {
        try {

            // Coba exact search
            $response = Http::timeout(20)->get(
                "https://restcountries.com/v3.1/name/" . urlencode($country),
                [
                    'fullText' => 'true'
                ]
            );

            // Kalau gagal, coba search biasa
            if (!$response->successful()) {

                $response = Http::timeout(20)->get(
                    "https://restcountries.com/v3.1/name/" . urlencode($country)
                );

            }

            if (!$response->successful()) {

                Log::error('Country API Error', [
                    'country' => $country,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if (!is_array($data) || empty($data)) {
                return null;
            }

            return $data[0];

        } catch (\Throwable $e) {

            Log::error($e->getMessage());

            return null;

        }
    }
}
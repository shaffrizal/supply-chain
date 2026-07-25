<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ComparisonController extends Controller
{
    /**
     * Menampilkan halaman awal dashboard perbandingan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil semua data negara diurutkan berdasarkan nama (sesuai kode awalmu)
        $countries = Country::orderBy('country_name')->get();

        return view('comparison.index', [
            'countries' => $countries,
            'country1'  => null,
            'country2'  => null,
            'winner'    => null, // Menambahkan variabel winner agar view tidak error
            'comparisonInsights' => [],
        ]);
    }

    /**
     * Memproses data perbandingan setelah form disubmit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function compare(Request $request, WeatherService $weatherService)
    {
        // 1. Validasi Input tingkat lanjut untuk memastikan ID aman dan ada di database
        $request->validate([
            'country1' => 'required|exists:countries,id',
            'country2' => ['required', 'exists:countries,id', Rule::notIn([$request->input('country1')])],
        ]);

        // 2. Mengambil kembali list negara untuk dropdown dengan pengurutan alfabet
        $countries = Country::orderBy('country_name')->get();

        // 3. Mengambil data negara yang dipilih (Menggunakan findOrFail untuk keamanan ekstra)
        $country1 = Country::findOrFail($request->input('country1'));
        $country2 = Country::findOrFail($request->input('country2'));

        // 4. Menentukan rekomendasi berdasarkan Risk Index terendah
$winner = $country1->risk_index <= $country2->risk_index
    ? $country1
    : $country2;

        $weather = collect($weatherService->globalConditions(collect([$country1, $country2])))
            ->keyBy(fn (array $point) => strtoupper($point['code']));
        $rates = Cache::remember('comparison.exchange.usd', now()->addHour(), function (): array {
            try {
                $response = Http::connectTimeout(2)->timeout(6)->get('https://open.er-api.com/v6/latest/USD');
                return $response->successful() ? (array) $response->json('rates') : [];
            } catch (\Throwable) {
                return [];
            }
        });
        $comparisonInsights = collect([$country1, $country2])->mapWithKeys(function (Country $country) use ($weather, $rates): array {
            $condition = $weather->get(strtoupper($country->country_code), []);
            return [$country->id => [
                'inflation' => $country->inflation_rate,
                'temperature' => $condition['temperature'] ?? null,
                'weather_risk' => ($condition['storm'] ?? false) ? 'Storm' : (($condition['rain'] ?? false) ? 'Rain' : 'Normal'),
                'currency' => $country->currency,
                'exchange_rate' => $rates[$country->currency] ?? null,
                'exports' => $country->exports_value,
                'imports' => $country->imports_value,
            ]];
        })->all();

        // 5. Mengirimkan semua data matang ke View
        return view('comparison.index', compact(
            'countries',
            'country1',
            'country2',
            'winner'
            ,'comparisonInsights'
        ));
    }
}

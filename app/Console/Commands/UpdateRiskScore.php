<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Services\RiskScoreService;
use Illuminate\Support\Facades\Http;

class UpdateRiskScore extends Command
{
    /**
     * Nama command
     */
    protected $signature = 'risk:update';

    /**
     * Deskripsi command
     */
    protected $description = 'Update Risk Score for all countries';

    /**
     * RiskScoreService
     */
    protected RiskScoreService $riskService;

    /**
     * Constructor
     */
    public function __construct(RiskScoreService $riskService)
    {
        parent::__construct();

        $this->riskService = $riskService;
    }

    /**
     * Main Command
     */
    public function handle()
    {
        $this->info('===================================');
        $this->info('Updating Supply Chain Risk Score...');
        $this->info('===================================');

        // Ambil seluruh negara
        $countries = Country::all();

        if ($countries->count() == 0) {

            $this->error('No countries found.');

            return Command::FAILURE;

        }

        $this->info(
            'Total Countries : '.$countries->count()
        );

                /*
        |--------------------------------------------------------------------------
        | Exchange Rate API
        |--------------------------------------------------------------------------
        */

        $exchangeRates = [];

        try {

            $exchange = Http::timeout(20)
                ->get('https://open.er-api.com/v6/latest/USD')
                ->json();

            if (
                isset($exchange['rates']) &&
                is_array($exchange['rates'])
            ) {

                $exchangeRates = $exchange['rates'];

                $this->info('Exchange Rate API Loaded.');

            } else {

                $this->warn('Exchange Rate API returned no rates.');

            }

        } catch (\Exception $e) {

            $this->warn(
                'Exchange Rate API Error : '.$e->getMessage()
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Loop Semua Negara
        |--------------------------------------------------------------------------
        */

        $sentimentCounts = NewsCache::selectRaw('sentiment, COUNT(*) AS total')->groupBy('sentiment')->pluck('total', 'sentiment');
        $newsSentiment = ($sentimentCounts['Negative'] ?? 0) > ($sentimentCounts['Positive'] ?? 0)
            ? 'negative'
            : (($sentimentCounts['Positive'] ?? 0) > ($sentimentCounts['Negative'] ?? 0) ? 'positive' : 'neutral');

        foreach ($countries as $country) {

            $this->line(
                'Processing : '.$country->country_name
            );

            /*
            |--------------------------------------------------------------------------
            | Default Values
            |--------------------------------------------------------------------------
            */

            $gdp = 0;

            $inflation = 0;

            $weather = 'Clouds';

            $exchangeRate = 1;

                        /*
            |--------------------------------------------------------------------------
            | GDP (World Bank)
            |--------------------------------------------------------------------------
            */

            try {

                $response = Http::timeout(20)->get(

                    "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/NY.GDP.MKTP.CD?format=json"

                );

                $json = $response->json();

                if (isset($json[1])) {

                    foreach ($json[1] as $item) {

                        if (!is_null($item['value'])) {

                            $gdp = $item['value'];

                            break;

                        }

                    }

                }

            } catch (\Exception $e) {

                $gdp = 0;

            }

            /*
            |--------------------------------------------------------------------------
            | Inflation (World Bank)
            |--------------------------------------------------------------------------
            */

            try {

                $response = Http::timeout(20)->get(

                    "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/FP.CPI.TOTL.ZG?format=json"

                );

                $json = $response->json();

                if (isset($json[1])) {

                    foreach ($json[1] as $item) {

                        if (!is_null($item['value'])) {

                            $inflation = $item['value'];

                            break;

                        }

                    }

                }

            } catch (\Exception $e) {

                $inflation = 0;

            }

                        /*
            |--------------------------------------------------------------------------
            | Weather (OpenWeather API)
            |--------------------------------------------------------------------------
            */

            try {

                $response = Http::timeout(20)->get(

                    'https://api.openweathermap.org/data/2.5/weather',

                    [

                        'lat' => $country->latitude,

                        'lon' => $country->longitude,

                        'appid' => env('OPENWEATHER_API_KEY'),

                        'units' => 'metric'

                    ]

                );

                $json = $response->json();

                if (isset($json['weather'][0]['main'])) {

                    $weather = $json['weather'][0]['main'];

                }

            } catch (\Exception $e) {

                $weather = 'Clouds';

            }

            /*
            |--------------------------------------------------------------------------
            | Exchange Rate
            |--------------------------------------------------------------------------
            */

            if (

                isset($exchangeRates[$country->currency])

            ) {

                $exchangeRate =

                    $exchangeRates[$country->currency];

            }

            /*
            |--------------------------------------------------------------------------
            | Calculate Risk Score
            |--------------------------------------------------------------------------
            */

            $weatherRisk = match ($weather) {
                'Thunderstorm' => 90, 'Rain' => 70, 'Drizzle' => 55,
                'Clouds' => 30, 'Clear' => 10, default => 40,
            };
            $previousExchangeRate = (float) ($country->exchange_rate ?: 0);
            $currencyRisk = $previousExchangeRate > 0 && $exchangeRate > 0
                ? min(100, abs(($exchangeRate - $previousExchangeRate) / $previousExchangeRate) * 1000)
                : 0;
            $risk = $this->riskService->calculate([
                'weather_risk' => $weatherRisk,
                'inflation' => $inflation,
                'currency_risk' => $currencyRisk,
                'news_sentiment' => $newsSentiment,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Database
            |--------------------------------------------------------------------------
            */

            $country->update([

                'risk_index' => $risk['score'],
                'risk_level' => $risk['level'],
                'exchange_rate' => $exchangeRate,

            ]);

            RiskScore::updateOrCreate([
                'country_id' => $country->id,
                'snapshot_date' => now()->startOfDay(),
            ], [
                'weather_risk' => $risk['components']['weather'],
                'inflation_risk' => $risk['components']['inflation'],
                'news_risk' => $risk['components']['news'],
                'currency_risk' => $risk['components']['currency'],
                'total_score' => $risk['score'],
                'risk_level' => $risk['level'],
            ]);

            $this->info(

                $country->country_name .

                ' => ' .

                $risk['score'] .

                ' (' .

                $risk['level'] .

                ')'

            );

                    }

        $retentionDate = now()->subDays(90)->startOfDay();
        $prunedSnapshots = RiskScore::where(function ($query) use ($retentionDate) {
            $query->where('snapshot_date', '<', $retentionDate->toDateString())
                ->orWhere(function ($query) use ($retentionDate) {
                    $query->whereNull('snapshot_date')->where('created_at', '<', $retentionDate);
                });
        })->delete();

        $this->info('');

        $this->info('======================================');

        $this->info('Risk Score Updated Successfully');

        $this->info('Total Countries : '.$countries->count());

        $this->info('Snapshot Date : '.now()->toDateString());

        $this->info('Old Snapshots Pruned : '.$prunedSnapshots);

        $this->info('======================================');

        return Command::SUCCESS;

    }

}

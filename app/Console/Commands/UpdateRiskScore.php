<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Services\RiskScoreService;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateRiskScore extends Command
{
    protected $signature = 'risk:update';
    protected $description = 'Update weighted supply-chain risk snapshots using Open-Meteo and public indicators';

    public function __construct(
        private readonly RiskScoreService $riskService,
        private readonly WeatherService $weatherService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $countries = Country::all();
        if ($countries->isEmpty()) {
            $this->error('No countries found.');
            return self::FAILURE;
        }

        $exchangeRates = $this->exchangeRates();
        $weatherByCountry = collect($this->weatherService->globalConditions($countries))
            ->keyBy(fn (array $point) => strtoupper($point['code']));
        $sentiments = NewsCache::selectRaw('sentiment, COUNT(*) AS total')->groupBy('sentiment')->pluck('total', 'sentiment');
        $newsSentiment = ($sentiments['Negative'] ?? 0) > ($sentiments['Positive'] ?? 0)
            ? 'negative'
            : (($sentiments['Positive'] ?? 0) > ($sentiments['Negative'] ?? 0) ? 'positive' : 'neutral');

        $bar = $this->output->createProgressBar($countries->count());
        foreach ($countries as $country) {
            $inflation = $this->latestWorldBankValue($country->country_code, 'FP.CPI.TOTL.ZG')
                ?? $country->inflation_rate
                ?? 0;
            $weather = $weatherByCountry->get(strtoupper($country->country_code), []);
            $weatherRisk = ($weather['storm'] ?? false)
                ? 90
                : (($weather['strong_wind'] ?? false) ? 75 : (($weather['rain'] ?? false) ? 65 : 20));
            $exchangeRate = (float) ($exchangeRates[$country->currency] ?? $country->exchange_rate ?? 1);
            $previousRate = (float) ($country->exchange_rate ?: 0);
            $currencyRisk = $previousRate > 0 && $exchangeRate > 0
                ? min(100, abs(($exchangeRate - $previousRate) / $previousRate) * 1000)
                : 0;

            $risk = $this->riskService->calculate([
                'weather_risk' => $weatherRisk,
                'inflation' => $inflation,
                'currency_risk' => $currencyRisk,
                'news_sentiment' => $newsSentiment,
            ]);

            $country->update([
                'risk_index' => $risk['score'],
                'risk_level' => $risk['level'],
                'exchange_rate' => $exchangeRate,
                'inflation_rate' => $inflation,
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
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $deleted = RiskScore::where(function ($query) {
            $cutoff = now()->subDays(90)->startOfDay();
            $query->where('snapshot_date', '<', $cutoff->toDateString())
                ->orWhere(fn ($query) => $query->whereNull('snapshot_date')->where('created_at', '<', $cutoff));
        })->delete();
        $this->info("Updated {$countries->count()} countries with Open-Meteo weather; pruned {$deleted} old snapshots.");

        return self::SUCCESS;
    }

    private function exchangeRates(): array
    {
        try {
            $response = Http::connectTimeout(3)->timeout(8)->get('https://open.er-api.com/v6/latest/USD');
            return $response->successful() ? (array) $response->json('rates') : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function latestWorldBankValue(string $countryCode, string $indicator): ?float
    {
        try {
            $response = Http::connectTimeout(3)->timeout(6)->get(
                "https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicator}",
                ['format' => 'json', 'per_page' => 10]
            );
            foreach (($response->json()[1] ?? []) as $row) {
                if (($row['value'] ?? null) !== null) return (float) $row['value'];
            }
        } catch (\Throwable) {
        }

        return null;
    }
}

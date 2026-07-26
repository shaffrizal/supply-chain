<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Services\RiskScoreService;
use App\Services\WeatherService;
use App\Services\CountryNewsRiskService;
use Illuminate\Console\Command;

class UpdateRiskScore extends Command
{
    protected $signature = 'risk:update';
    protected $description = 'Update weighted supply-chain risk snapshots using Open-Meteo and public indicators';

    public function __construct(
        private readonly RiskScoreService $riskService,
        private readonly WeatherService $weatherService,
        private readonly CountryNewsRiskService $countryNewsRisk,
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
        $articles = NewsCache::query()
            ->where('published_at', '>=', now()->subDays(30))
            ->latest('published_at')
            ->get(['keyword', 'title', 'description']);

        $bar = $this->output->createProgressBar($countries->count());
        foreach ($countries as $country) {
            $countryNews = $this->countryNewsRisk->analyze($country, $articles);
            // World Bank values are refreshed in one batch by worldbank:sync.
            // Reusing the persisted value avoids up to 250 provider requests here.
            $inflation = $country->inflation_rate ?? 0;
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
                'news_sentiment' => $countryNews['sentiment'],
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

}

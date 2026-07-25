<?php

namespace App\Services;

use App\Models\RiskScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrendDataService
{
    public function currency(string $base, string $quote, int $days = 30, bool $fetchWhenMissing = true): array
    {
        $base = strtoupper($base);
        $quote = strtoupper($quote);

        if ($base === $quote) {
            return [];
        }

        $end = now()->toDateString();
        $start = now()->subDays($days)->toDateString();

        $cacheKey = "trend.currency.v2.$base.$quote.$start.$end";

        if (! $fetchWhenMissing) {
            return Cache::get($cacheKey, []);
        }

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($base, $quote, $start, $end) {
            try {
                $response = Http::connectTimeout(3)->timeout(12)
                    ->get('https://api.frankfurter.dev/v2/rates', [
                        'from' => $start,
                        'to' => $end,
                        'base' => $base,
                        'quotes' => $quote,
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                $points = [];
                foreach ((array) $response->json() as $row) {
                    if (is_array($row) && isset($row['date'], $row['rate']) && ($row['quote'] ?? $quote) === $quote) {
                        $points[] = ['date' => (string) $row['date'], 'value' => (float) $row['rate']];
                    }
                }

                usort($points, fn (array $a, array $b) => $a['date'] <=> $b['date']);

                return $points;
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public function risk(?int $countryId = null, int $days = 90): array
    {
        $scores = RiskScore::query()
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->where(function ($query) use ($days) {
                $query->where('snapshot_date', '>=', now()->subDays($days)->toDateString())
                    ->orWhere(function ($query) use ($days) {
                        $query->whereNull('snapshot_date')->where('created_at', '>=', now()->subDays($days)->startOfDay());
                    });
            })
            ->orderByRaw('COALESCE(snapshot_date, DATE(created_at))')
            ->get(['total_score', 'snapshot_date', 'created_at']);

        return $scores
            ->groupBy(fn (RiskScore $score) => $score->snapshot_date?->toDateString() ?? $score->created_at->toDateString())
            ->map(fn (Collection $daily, string $date) => [
                'date' => $date,
                'value' => round((float) $daily->avg('total_score'), 2),
                'samples' => $daily->count(),
            ])
            ->values()
            ->all();
    }
}

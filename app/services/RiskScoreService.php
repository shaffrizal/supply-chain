<?php

namespace App\Services;

class RiskScoreService
{
    public function calculate(array $indicators = []): array
    {
        $weather = min(100, max(0, (float) ($indicators['weather_risk'] ?? 0)));
        $inflation = min(100, max(0, abs((float) ($indicators['inflation'] ?? 0)) * 10));
        $currency = min(100, max(0, (float) ($indicators['currency_risk'] ?? 0)));
        $news = match (strtolower((string) ($indicators['news_sentiment'] ?? 'neutral'))) {
            'negative' => 100, 'positive' => 0, default => 50,
        };
        $score = round(($weather * .30) + ($inflation * .20) + ($news * .40) + ($currency * .10), 2);
        $level = $score >= 70 ? 'High' : ($score >= 40 ? 'Medium' : 'Low');

        return [
            'score' => $score,
            'level' => $level,
            'components' => compact('weather', 'inflation', 'news', 'currency'),
        ];
    }
}

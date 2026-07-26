<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Collection;

class CountryNewsRiskService
{
    private const ALIASES = [
        'United States' => ['united states', 'usa', 'u.s.', 'american', 'washington'],
        'United Kingdom' => ['united kingdom', 'uk', 'u.k.', 'britain', 'british'],
        'South Korea' => ['south korea', 'republic of korea', 'korean', 'seoul'],
        'North Korea' => ['north korea', 'dprk', 'pyongyang'],
        'United Arab Emirates' => ['united arab emirates', 'uae', 'dubai', 'abu dhabi'],
        'Russia' => ['russia', 'russian', 'moscow'],
        'China' => ['china', 'chinese', 'beijing', 'shanghai'],
        'Indonesia' => ['indonesia', 'indonesian', 'jakarta'],
        'Singapore' => ['singapore', 'singaporean'],
        'Saudi Arabia' => ['saudi arabia', 'saudi', 'riyadh'],
    ];

    public function __construct(
        private readonly SentimentAnalysisService $sentiment,
    ) {
    }

    public function analyze(Country $country, Collection $articles): array
    {
        $terms = self::ALIASES[$country->country_name] ?? [mb_strtolower($country->country_name)];
        $terms[] = mb_strtolower($country->country_name);
        $terms = array_values(array_unique($terms));

        $relevant = $articles->filter(function ($article) use ($terms) {
            $haystack = mb_strtolower(implode(' ', [
                $article->keyword,
                $article->title,
                $article->description,
            ]));

            foreach ($terms as $term) {
                if (preg_match('/(?<![\pL\pN])'.preg_quote($term, '/').'(?![\pL\pN])/u', $haystack) === 1) {
                    return true;
                }
            }

            return false;
        });

        if ($relevant->isEmpty()) {
            return [
                'sentiment' => 'Neutral',
                'positive_score' => 0,
                'negative_score' => 0,
                'article_count' => 0,
            ];
        }

        $analysis = $this->sentiment->analyze(
            $relevant->map(fn ($article) => trim($article->title.' '.$article->description))->implode(' ')
        );

        return [...$analysis, 'article_count' => $relevant->count()];
    }
}

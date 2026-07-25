<?php

namespace App\Services;

class SentimentAnalysisService
{
    private array $positiveWords = ['growth', 'increase', 'increased', 'profit', 'stable', 'improve', 'improved', 'recovery', 'gain', 'strong'];
    private array $negativeWords = ['war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'decreased', 'conflict', 'shortage', 'disruption'];

    public function analyze(string $text): array
    {
        $words = preg_split('/[^a-z]+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $positive = count(array_intersect($words, $this->positiveWords));
        $negative = count(array_intersect($words, $this->negativeWords));

        return [
            'positive_score' => $positive,
            'negative_score' => $negative,
            'sentiment' => $positive === $negative ? 'Neutral' : ($positive > $negative ? 'Positive' : 'Negative'),
        ];
    }
}

<?php

namespace App\Services;

class SentimentAnalysisService
{
    private array $positiveWords = [
        'growth', 'increase', 'increased', 'profit', 'stable', 'improve', 'improved',
        'recovery', 'recover', 'gain', 'strong', 'surplus', 'investment', 'resilient',
        'expansion', 'efficient', 'secured', 'agreement', 'stabilized',
        'pertumbuhan', 'meningkat', 'keuntungan', 'stabil', 'membaik', 'pemulihan',
        'pulih', 'surplus', 'investasi', 'tangguh', 'efisien', 'aman',
    ];
    private array $negativeWords = [
        'war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'decreased',
        'conflict', 'shortage', 'disruption', 'blockade', 'congestion', 'strike',
        'sanction', 'sanctions', 'embargo', 'volatile', 'collapse', 'attack',
        'krisis', 'inflasi', 'tertunda', 'keterlambatan', 'bencana', 'menurun',
        'konflik', 'kelangkaan', 'gangguan', 'blokade', 'kemacetan', 'mogok',
        'sanksi', 'serangan',
    ];

    public function analyze(string $text): array
    {
        $words = preg_split('/[^\pL]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        $positiveLookup = array_fill_keys($this->positiveWords, true);
        $negativeLookup = array_fill_keys($this->negativeWords, true);
        $positive = 0;
        $negative = 0;

        foreach ($words as $word) {
            $positive += isset($positiveLookup[$word]) ? 1 : 0;
            $negative += isset($negativeLookup[$word]) ? 1 : 0;
        }

        return [
            'positive_score' => $positive,
            'negative_score' => $negative,
            'sentiment' => $positive === $negative ? 'Neutral' : ($positive > $negative ? 'Positive' : 'Negative'),
        ];
    }
}

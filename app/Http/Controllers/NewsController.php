<?php

namespace App\Http\Controllers;

use App\Models\NewsCache;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class NewsController extends Controller
{
    public function index(Request $request, SentimentAnalysisService $sentimentService)
    {
        $topics = [
            'supply chain' => 'Supply Chain',
            'logistics' => 'Logistics',
            'shipping' => 'Shipping',
            'global trade' => 'Global Trade',
            'economy' => 'Economy',
            'geopolitics' => 'Geopolitics',
        ];

        $topic = $request->string('topic')->lower()->value();
        $topic = array_key_exists($topic, $topics) ? $topic : 'supply chain';
        $search = trim($request->string('search')->value());
        $keyword = $search !== '' ? Str::limit($search, 100, '') : $topic;
        $gnewsKey = config('services.gnews.key');
        $newsApiKey = config('services.newsapi.key');
        $providerName = filled($newsApiKey) ? 'NewsAPI' : 'GNews fallback';
        $providerTotal = 0;
        $apiAvailable = false;

        try {
            $payload = Cache::remember(
                'news-intelligence.v2.'.sha1(Str::lower($keyword)),
                now()->addMinutes(20),
                function () use ($gnewsKey, $newsApiKey, $keyword) {
                    if (filled($newsApiKey)) {
                        $response = Http::connectTimeout(3)->timeout(10)->retry(1, 250)->get('https://newsapi.org/v2/everything', [
                            'q' => $keyword,
                            'language' => 'en',
                            'sortBy' => 'publishedAt',
                            'pageSize' => 24,
                            'apiKey' => $newsApiKey,
                        ]);

                        if ($response->successful()) {
                            $payload = $response->json();
                            $payload['provider'] = 'NewsAPI';

                            return $payload;
                        }
                    }

                    if (filled($gnewsKey)) {
                        $response = Http::connectTimeout(3)->timeout(10)->retry(1, 250)
                            ->get('https://gnews.io/api/v4/search', [
                                'q' => $keyword,
                                'lang' => 'en',
                                'max' => 10,
                                'sortby' => 'publishedAt',
                                'apikey' => $gnewsKey,
                            ]);

                        if ($response->successful()) {
                            $payload = $response->json();
                            $payload['provider'] = 'GNews fallback';

                            return $payload;
                        }
                    }

                    return null;
                }
            );

            $providerName = $payload['provider'] ?? $providerName;
            $apiAvailable = is_array($payload) && isset($payload['articles']);
            $providerTotal = (int) ($payload['totalArticles'] ?? $payload['totalResults'] ?? 0);
            $articles = collect($payload['articles'] ?? []);
        } catch (Throwable) {
            $articles = collect();
        }

        $articles = $articles
            ->filter(fn (array $article) => filled($article['title'] ?? null) && filled($article['url'] ?? null))
            ->map(function (array $article) use ($sentimentService, $keyword) {
                $article['urlToImage'] = $article['urlToImage'] ?? $article['image'] ?? null;
                $analysis = $sentimentService->analyze(
                    trim(($article['title'] ?? '').' '.($article['description'] ?? ''))
                );
                $article['sentiment'] = $analysis['sentiment'];

                try {
                    NewsCache::updateOrCreate(
                        ['url' => $article['url']],
                        [
                            'keyword' => $keyword,
                            'title' => $article['title'],
                            'description' => $article['description'] ?? null,
                            'image_url' => $article['urlToImage'] ?? null,
                            'sentiment' => $article['sentiment'],
                            'published_at' => $article['publishedAt'] ?? now(),
                        ]
                    );
                } catch (Throwable) {
                    // The live news page must remain usable when its local cache is unavailable.
                }

                return $article;
            })
            ->values();

        if ($articles->isEmpty()) {
            try {
                $articles = NewsCache::query()
                    ->when($keyword, fn ($query) => $query->where(function ($query) use ($keyword) {
                        $query->where('keyword', 'like', "%{$keyword}%")
                            ->orWhere('title', 'like', "%{$keyword}%");
                    }))
                    ->latest('published_at')
                    ->limit(24)
                    ->get()
                    ->map(fn (NewsCache $article) => [
                        'title' => $article->title,
                        'description' => $article->description,
                        'url' => $article->url,
                        'urlToImage' => $article->image_url,
                        'publishedAt' => optional($article->published_at)->toIso8601String(),
                        'source' => ['name' => parse_url($article->url, PHP_URL_HOST) ?: 'Cached source'],
                        'sentiment' => $article->sentiment ?: 'Neutral',
                    ])
                    ->values();
            } catch (Throwable) {
                $articles = collect();
            }
        }

        $sentimentCounts = [
            'Positive' => $articles->where('sentiment', 'Positive')->count(),
            'Neutral' => $articles->where('sentiment', 'Neutral')->count(),
            'Negative' => $articles->where('sentiment', 'Negative')->count(),
        ];
        $analyzedCount = $articles->count();
        $sentimentPercentages = collect($sentimentCounts)->map(
            fn (int $count) => $analyzedCount > 0 ? round(($count / $analyzedCount) * 100) : 0
        )->all();
        $topSources = $articles
            ->countBy(fn (array $article) => data_get($article, 'source.name', 'Unknown source'))
            ->sortDesc()
            ->take(6);
        $dominantSentiment = collect($sentimentCounts)->sortDesc()->keys()->first() ?? 'Neutral';

        return view('news.index', compact(
            'articles',
            'topics',
            'topic',
            'keyword',
            'search',
            'sentimentCounts',
            'sentimentPercentages',
            'analyzedCount',
            'providerTotal',
            'topSources',
            'dominantSentiment',
            'apiAvailable'
            ,'providerName'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Models\NewsCache;
use App\Models\ShippingRoute;
use App\Models\Watchlist;
use App\Services\TrendDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request, TrendDataService $trends)
    {
        $selectedCountry = Country::when($request->filled('country'), fn ($query) =>
            $query->where('country_code', strtoupper((string) $request->string('country')))
        )->orderByRaw("country_code = 'ID' DESC")->orderBy('country_name')->first();

        $riskCounts = Country::selectRaw("CASE WHEN risk_index >= 70 THEN 'High' WHEN risk_index >= 40 THEN 'Medium' ELSE 'Low' END AS level, COUNT(*) AS total")
            ->groupBy('level')->pluck('total', 'level');

        // The command center must never block its initial render on third-party APIs.
        // Dedicated feature pages warm these caches; the global runtime refreshes
        // database-backed metrics asynchronously after the page is visible.
        $weather = $selectedCountry
            ? Cache::get('dashboard.weather.'.strtolower($selectedCountry->country_code), [])
            : [];
        $exchange = Cache::get('dashboard.exchange.usd', []);
        $newsSentiments = NewsCache::selectRaw('sentiment, COUNT(*) AS total')
            ->groupBy('sentiment')->pluck('total', 'sentiment');
        $trendQuote = $selectedCountry?->currency ?: 'IDR';
        $currencyTrend = $trendQuote === 'USD' ? [] : $trends->currency('USD', $trendQuote, 30, false);

        return view('dashboard.dashboard', [
            'totalCountry' => Country::count(),
            'totalPorts' => Port::count(),
            'activePorts' => Port::where('status', 'Active')->count(),
            'highRiskPorts' => Port::where('risk_index', '>=', 70)->count(),
            'totalRoutes' => ShippingRoute::count(),
            'activeRoutes' => ShippingRoute::where('route_status', 'Active')->count(),
            'watchlistCount' => Watchlist::count(),
            'newsToday' => NewsCache::whereDate('created_at', today())->count(),
            'totalNews' => NewsCache::count(),
            'averageRisk' => round((float) Country::avg('risk_index'), 1),
            'lowRisk' => (int) ($riskCounts['Low'] ?? 0),
            'mediumRisk' => (int) ($riskCounts['Medium'] ?? 0),
            'highRisk' => (int) ($riskCounts['High'] ?? 0),
            'topRiskCountries' => Country::orderByDesc('risk_index')->take(6)->get(),
            'countries' => Country::orderBy('country_name')->get(['country_code', 'country_name', 'flag']),
            'selectedCountry' => $selectedCountry,
            'selectedPortCount' => $selectedCountry?->ports()->count() ?? 0,
            'latestNews' => NewsCache::latest('published_at')->take(5)->get(),
            'newsSentiments' => $newsSentiments,
            'latestPorts' => Port::query()->orderByDesc('id')->take(6)->get(),
            'weather' => $weather,
            'exchange' => $exchange,
            'trendQuote' => $trendQuote,
            'currencyTrend' => $currencyTrend,
            'dataSources' => ['REST Countries', 'Open-Meteo', 'World Bank', 'Exchange Rate API', 'NewsAPI', 'OpenStreetMap'],
        ]);
    }

}

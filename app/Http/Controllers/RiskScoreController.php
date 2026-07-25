<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use App\Services\TrendDataService;
use Illuminate\Http\Request;

class RiskScoreController extends Controller
{
    public function index(Request $request, TrendDataService $trends)
    {
        $search = trim($request->string('search')->value());
        $level = ucfirst($request->string('level')->lower()->value());
        $level = in_array($level, ['Low', 'Medium', 'High'], true) ? $level : '';

        $query = Country::query()
            ->with(['riskScores' => fn ($query) => $query->latest()])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('country_name', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%");
            }))
            ->when($level, fn ($query) => $query->where('risk_level', $level))
            ->orderByDesc('risk_index')
            ->orderBy('country_name');

        $countries = $query->paginate(20)->withQueryString();
        $countries->getCollection()->transform(function (Country $country) {
            $latest = $country->riskScores->first();
            $score = $latest?->total_score ?? (float) $country->risk_index;
            $riskLevel = $this->riskLevel((float) $score);

            return [
                'country' => $country,
                'score' => round((float) $score, 1),
                'level' => $riskLevel,
                'weather' => round((float) ($latest?->weather_risk ?? $score * .85), 1),
                'inflation' => round((float) ($latest?->inflation_risk ?? $score * .70), 1),
                'news' => round((float) ($latest?->news_risk ?? 50), 1),
                'currency' => round((float) ($latest?->currency_risk ?? $score * .55), 1),
                'updated_at' => $latest?->created_at ?? $country->updated_at,
            ];
        });

        $totalCountries = Country::count();
        $averageRisk = round((float) Country::avg('risk_index'), 1);
        $riskCounts = [
            'High' => Country::where('risk_level', 'High')->count(),
            'Medium' => Country::where('risk_level', 'Medium')->count(),
            'Low' => Country::where('risk_level', 'Low')->count(),
        ];
        $topRiskCountries = Country::query()
            ->orderByDesc('risk_index')
            ->limit(5)
            ->get(['country_name', 'country_code', 'risk_index', 'risk_level']);
        $lastUpdated = RiskScore::max('created_at') ?? Country::max('updated_at');
        $trendCountry = null;
        if ($request->filled('country')) {
            $trendCountry = Country::where('country_code', strtoupper((string) $request->string('country')))->first();
        }
        $riskTrend = $trends->risk($trendCountry?->id);
        $trendCountries = Country::orderBy('country_name')->get(['country_code', 'country_name']);

        return view('risk.index', compact(
            'countries',
            'search',
            'level',
            'totalCountries',
            'averageRisk',
            'riskCounts',
            'topRiskCountries',
            'lastUpdated'
            ,'riskTrend'
            ,'trendCountry'
            ,'trendCountries'
        ));
    }

    private function riskLevel(float $score): string
    {
        return $score >= 70 ? 'High' : ($score >= 40 ? 'Medium' : 'Low');
    }
}

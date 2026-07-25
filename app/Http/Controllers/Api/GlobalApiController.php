<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\Port;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Models\ShippingRoute;
use Illuminate\Http\JsonResponse;

class GlobalApiController extends Controller
{
public function getCountries(Request $request)
{
    $query = \DB::table('countries');

    if ($request->has('search')) {
        $searchTerm = $request->input('search');
        $query->where('country_name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('country_code', 'LIKE', '%' . $searchTerm . '%');
    }

    $countries = $query->get();

    return response()->json([
        'status' => 'success',
        'data' => $countries
    ]);
}

public function getRisk() { return response()->json(['status'=>'success','data'=>RiskScore::with('country')->latest()->get()]); }
public function getPorts(Request $request) { return response()->json(['status'=>'success','data'=>Port::query()->when($request->search, fn($q,$s)=>$q->where('port_name','like',"%$s%")->orWhere('country','like',"%$s%"))->get()]); }
public function getNews(Request $request) { return response()->json(['status'=>'success','data'=>NewsCache::where('keyword','like','%'.$request->get('search','').'%')->latest('published_at')->get()]); }

public function getOverview(): JsonResponse
{
    $riskCounts = Country::query()
        ->selectRaw("CASE WHEN risk_index >= 70 THEN 'high' WHEN risk_index >= 40 THEN 'medium' ELSE 'low' END AS level, COUNT(*) AS total")
        ->groupBy('level')
        ->pluck('total', 'level');

    return response()->json([
        'status' => 'success',
        'data' => [
            'countries' => Country::count(),
            'ports' => Port::count(),
            'active_ports' => Port::where('status', 'Active')->count(),
            'high_risk_ports' => Port::where('risk_index', '>=', 70)->count(),
            'routes' => ShippingRoute::count(),
            'active_routes' => ShippingRoute::where('route_status', 'Active')->count(),
            'news' => NewsCache::count(),
            'news_today' => NewsCache::whereDate('created_at', today())->count(),
            'average_risk' => round((float) Country::avg('risk_index'), 1),
            'low_risk' => (int) ($riskCounts['low'] ?? 0),
            'medium_risk' => (int) ($riskCounts['medium'] ?? 0),
            'high_risk' => (int) ($riskCounts['high'] ?? 0),
            'updated_at' => now()->toIso8601String(),
        ],
    ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
}

public function getCurrency(Request $request)
{
    $base = strtoupper($request->get('base', 'USD'));
    if (! preg_match('/^[A-Z]{3}$/', $base)) {
        return response()->json(['status'=>'error','message'=>'Invalid currency base'], 422);
    }
    try {
        $response = Http::connectTimeout(2)->timeout(6)->get("https://open.er-api.com/v6/latest/$base");
    } catch (\Throwable) {
        return response()->json(['status'=>'error','message'=>'Currency provider unavailable'], 503);
    }
    return $response->successful() ? response()->json(['status'=>'success','base'=>$base,'data'=>$response->json('rates')]) : response()->json(['status'=>'error','message'=>'Currency provider unavailable'], 503);
}
}

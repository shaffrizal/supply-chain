<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\ShippingRoute;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const TYPES = [
        'executive', 'risk', 'economy', 'ports', 'news', 'watchlist', 'articles', 'users',
    ];

    public function index(): View
    {
        return view('reports.index', [
            'stats' => [
                'countries' => Country::count(),
                'ports' => Port::count(),
                'highRisk' => Country::where('risk_index', '>=', 70)->count(),
                'news' => NewsCache::count(),
                'reports' => auth()->user()?->role === 'Admin' ? 8 : 6,
            ],
            'regions' => Country::whereNotNull('region')->distinct()->orderBy('region')->pluck('region'),
        ]);
    }

    public function print(Request $request, string $type): View
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        if (in_array($type, ['users', 'articles'], true)) {
            abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
        }

        $search = trim($request->string('search')->value());
        $region = trim($request->string('region')->value());
        $level = ucfirst($request->string('level')->lower()->value());
        $level = in_array($level, ['Low', 'Medium', 'High'], true) ? $level : '';
        $status = in_array($request->input('status'), ['Active', 'Limited', 'Inactive'], true)
            ? $request->input('status') : '';

        $payload = match ($type) {
            'executive' => $this->executive(),
            'risk' => $this->risk($search, $region, $level),
            'economy' => $this->economy($search, $region),
            'ports' => $this->ports($search, $status, $level),
            'news' => $this->news($search),
            'watchlist' => $this->watchlist(),
            'articles' => $this->articles($search),
            'users' => $this->users($search),
        };

        return view('reports.print', [
            'type' => $type,
            'payload' => $payload,
            'generatedAt' => now(),
            'filters' => compact('search', 'region', 'level', 'status'),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        if (in_array($type, ['users', 'articles'], true)) {
            abort_unless(auth()->check() && auth()->user()->role === 'Admin', 403);
        }

        $search = trim($request->string('search')->value());
        $region = trim($request->string('region')->value());
        $level = ucfirst($request->string('level')->lower()->value());
        $level = in_array($level, ['Low', 'Medium', 'High'], true) ? $level : '';
        $status = in_array($request->input('status'), ['Active', 'Limited', 'Inactive'], true) ? $request->input('status') : '';
        $payload = match ($type) {
            'executive' => $this->executive(), 'risk' => $this->risk($search, $region, $level),
            'economy' => $this->economy($search, $region), 'ports' => $this->ports($search, $status, $level),
            'news' => $this->news($search), 'watchlist' => $this->watchlist(),
            'articles' => $this->articles($search), 'users' => $this->users($search),
        };
        [$headers, $rows] = $this->csvData($type, $payload['rows']);

        return response()->streamDownload(function () use ($headers, $rows) {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers);
            foreach ($rows as $row) fputcsv($stream, $row);
            fclose($stream);
        }, 'supply-chain-'.$type.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvData(string $type, $rows): array
    {
        return match ($type) {
            'risk', 'executive' => [['Country Code', 'Country', 'Region', 'Risk Score', 'Risk Level'], $rows->map(fn ($r) => [$r->country_code, $r->country_name, $r->region, $r->risk_index, $r->risk_level])],
            'economy' => [['Country Code', 'Country', 'Region', 'GDP', 'Population', 'Inflation %', 'GDP Growth %', 'Trade/GDP %', 'Exports', 'Imports'], $rows->map(fn ($r) => [$r->country_code, $r->country_name, $r->region, $r->gdp, $r->population, $r->inflation_rate, $r->gdp_growth, $r->trade_percentage, $r->exports_value, $r->imports_value])],
            'ports' => [['Port Code', 'Port', 'City', 'Country', 'Status', 'Risk Score', 'Latitude', 'Longitude'], $rows->map(fn ($r) => [$r->port_code, $r->port_name, $r->city, $r->country, $r->status, $r->risk_index, $r->latitude, $r->longitude])],
            'news' => [['Published At', 'Keyword', 'Title', 'Source', 'Sentiment', 'URL'], $rows->map(fn ($r) => [$r->published_at, $r->keyword, $r->title, $r->source, $r->sentiment, $r->url])],
            'watchlist' => [['Country Code', 'Country', 'Region', 'Risk Score', 'Risk Level', 'Added At'], $rows->map(fn ($r) => [$r->country?->country_code, $r->country?->country_name, $r->country?->region, $r->country?->risk_index, $r->country?->risk_level, $r->created_at])],
            'articles' => [['Title', 'Category', 'Author', 'Published At'], $rows->map(fn ($r) => [$r->title, $r->category, $r->author, $r->created_at])],
            'users' => [['Name', 'Email', 'Role', 'Department', 'Status', 'Last Login'], $rows->map(fn ($r) => [$r->name, $r->email, $r->role, $r->department, $r->status, $r->last_login_at])],
        };
    }

    private function executive(): array
    {
        $riskCounts = Country::selectRaw("CASE WHEN risk_index >= 70 THEN 'High' WHEN risk_index >= 40 THEN 'Medium' ELSE 'Low' END level, COUNT(*) total")
            ->groupBy('level')->pluck('total', 'level');

        return [
            'title' => 'Executive Intelligence Summary',
            'metrics' => [
                'Countries' => Country::count(),
                'Ports' => Port::count(),
                'Active Routes' => ShippingRoute::where('route_status', 'Active')->count(),
                'Average Risk' => number_format((float) Country::avg('risk_index'), 1),
                'High Risk' => (int) ($riskCounts['High'] ?? 0),
                'News Records' => NewsCache::count(),
            ],
            'rows' => Country::orderByDesc('risk_index')->limit(15)->get(),
        ];
    }

    private function risk(string $search, string $region, string $level): array
    {
        $rows = Country::query()
            ->with(['riskScores' => fn ($query) => $query->latest()->limit(1)])
            ->when($search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('country_name', 'like', "%{$search}%")
                ->orWhere('country_code', 'like', "%{$search}%")))
            ->when($region, fn ($query) => $query->where('region', $region))
            ->when($level, fn ($query) => $query->where('risk_level', $level))
            ->orderByDesc('risk_index')->get();

        return [
            'title' => 'Global Risk Assessment',
            'metrics' => [
                'Countries' => $rows->count(),
                'Average Risk' => number_format((float) $rows->avg('risk_index'), 1),
                'High Risk' => $rows->where('risk_level', 'High')->count(),
                'Measured Snapshots' => $rows->filter(fn ($row) => $row->riskScores->isNotEmpty())->count(),
            ],
            'rows' => $rows,
        ];
    }

    private function economy(string $search, string $region): array
    {
        $rows = Country::query()
            ->when($search, fn ($query) => $query->where('country_name', 'like', "%{$search}%"))
            ->when($region, fn ($query) => $query->where('region', $region))
            ->orderByDesc('gdp')->get();

        return [
            'title' => 'Global Economy Intelligence',
            'metrics' => [
                'Economies' => $rows->count(),
                'Combined GDP' => '$'.number_format((float) $rows->sum('gdp') / 1e12, 1).'T',
                'Population' => number_format((float) $rows->sum('population') / 1e9, 2).'B',
                'Average Inflation' => number_format((float) $rows->whereNotNull('inflation_rate')->avg('inflation_rate'), 2).'%',
            ],
            'rows' => $rows,
        ];
    }

    private function ports(string $search, string $status, string $level): array
    {
        $query = Port::query()
            ->when($search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('port_name', 'like', "%{$search}%")
                ->orWhere('port_code', 'like', "%{$search}%")
                ->orWhere('country', 'like', "%{$search}%")))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($level === 'High', fn ($query) => $query->where('risk_index', '>=', 70))
            ->when($level === 'Medium', fn ($query) => $query->whereBetween('risk_index', [40, 69]))
            ->when($level === 'Low', fn ($query) => $query->where('risk_index', '<', 40));
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('risk_index')->limit(500)->get();

        return [
            'title' => 'Port Risk & Operations Report',
            'metrics' => [
                'Matching Records' => $total,
                'Printed Records' => $rows->count(),
                'Active' => $rows->where('status', 'Active')->count(),
                'High Risk' => $rows->where('risk_index', '>=', 70)->count(),
            ],
            'rows' => $rows,
            'notice' => $total > 500 ? 'The detailed section is limited to the top 500 matching records.' : null,
        ];
    }

    private function news(string $search): array
    {
        $rows = NewsCache::query()
            ->when($search, fn ($query) => $query->where(fn ($nested) => $nested
                ->where('title', 'like', "%{$search}%")
                ->orWhere('keyword', 'like', "%{$search}%")))
            ->latest('published_at')->limit(200)->get();

        return [
            'title' => 'News Sentiment Intelligence',
            'metrics' => [
                'Articles' => $rows->count(),
                'Positive' => $rows->where('sentiment', 'Positive')->count(),
                'Neutral' => $rows->where('sentiment', 'Neutral')->count(),
                'Negative' => $rows->where('sentiment', 'Negative')->count(),
            ],
            'rows' => $rows,
        ];
    }

    private function watchlist(): array
    {
        $query = Watchlist::with('country');
        auth()->check()
            ? $query->where('user_id', auth()->id())
            : $query->where('session_id', session('watchlist_owner_key', 'missing'));
        $rows = $query->latest()->get();

        return [
            'title' => 'Priority Watchlist Report',
            'metrics' => [
                'Countries' => $rows->count(),
                'High Risk' => $rows->filter(fn ($row) => $row->country?->risk_level === 'High')->count(),
                'Regions' => $rows->pluck('country.region')->filter()->unique()->count(),
                'Average Risk' => number_format((float) $rows->pluck('country.risk_index')->filter()->avg(), 1),
            ],
            'rows' => $rows,
        ];
    }

    private function articles(string $search): array
    {
        $rows = Article::query()
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()->limit(200)->get();
        return [
            'title' => 'Intelligence Brief Report',
            'metrics' => ['Briefs' => $rows->count(), 'Authors' => $rows->pluck('author')->unique()->count(), 'Categories' => $rows->pluck('category')->unique()->count()],
            'rows' => $rows,
        ];
    }

    private function users(string $search): array
    {
        $rows = User::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->latest()->get();
        return [
            'title' => 'Identity & Access Report',
            'metrics' => ['Accounts' => $rows->count(), 'Administrators' => $rows->where('role', 'Admin')->count(), 'Analysts' => $rows->where('role', 'Analyst')->count(), 'Departments' => $rows->pluck('department')->filter()->unique()->count()],
            'rows' => $rows,
        ];
    }
}

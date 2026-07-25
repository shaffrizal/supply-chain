<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WatchlistController extends Controller
{
    public function index()
    {
        $watchlists = $this->query()->with('country')->latest()->get();
        $countries = $watchlists->pluck('country')->filter();
        $recommendations = Country::whereNotIn('id', $watchlists->pluck('country_id'))
            ->orderByDesc('risk_index')->take(4)->get();
        $stats = [
            'total' => $countries->count(),
            'high' => $countries->where('risk_level', 'High')->count(),
            'average' => round((float) $countries->avg('risk_index'), 1),
            'regions' => $countries->pluck('region')->filter()->unique()->count(),
        ];

        return view('watchlists.index', compact('watchlists', 'recommendations', 'stats'));
    }

    public function store(Request $request, Country $country)
    {
        $watchlist = Watchlist::firstOrCreate([
            'user_id' => auth()->id(),
            'country_id' => $country->id,
            'session_id' => auth()->check() ? null : $this->ownerKey(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "$country->country_name added to monitoring.",
                'watchlist_id' => $watchlist->id,
            ], $watchlist->wasRecentlyCreated ? 201 : 200);
        }

        return back()->with('success', "$country->country_name ditambahkan ke monitoring list.");
    }

    public function destroy(Request $request, Watchlist $watchlist)
    {
        abort_unless($this->query()->whereKey($watchlist->id)->exists(), 403);
        $watchlist->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Country removed from monitoring.']);
        }

        return back()->with('success', 'Negara dihapus dari monitoring list.');
    }

    private function query()
    {
        return Watchlist::query()->when(
            auth()->check(),
            fn ($query) => $query->where('user_id', auth()->id()),
            fn ($query) => $query->where('session_id', $this->ownerKey())
        );
    }

    private function ownerKey(): string
    {
        if (! session()->has('watchlist_owner_key')) {
            session()->put('watchlist_owner_key', (string) Str::uuid());
        }

        return (string) session('watchlist_owner_key');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function index()
    {
        $apiKey = env('NEWS_API_KEY');

        $response = Http::get('https://newsapi.org/v2/everything', [
            'q' => 'supply chain',
            'language' => 'en',
            'sortBy' => 'publishedAt',
            'pageSize' => 10,
            'apiKey' => $apiKey,
        ]);

        if ($response->failed()) {
            return back()->with('error', 'Gagal mengambil berita.');
        }

        $data = $response->json();

        return view('news.index', compact('data'));
    }
}
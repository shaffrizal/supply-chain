<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $apiKey = env('EXCHANGE_RATE_API_KEY');

        $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD");

        if ($response->failed()) {
            return back()->with('error', 'Gagal mengambil data kurs.');
        }

        $data = $response->json();

        return view('exchange.index', compact('data'));
    }
}
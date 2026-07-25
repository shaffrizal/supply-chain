<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    /**
     * Menampilkan halaman awal dashboard perbandingan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil semua data negara diurutkan berdasarkan nama (sesuai kode awalmu)
        $countries = Country::orderBy('country_name')->get();

        return view('comparison.index', [
            'countries' => $countries,
            'country1'  => null,
            'country2'  => null,
            'winner'    => null, // Menambahkan variabel winner agar view tidak error
        ]);
    }

    /**
     * Memproses data perbandingan setelah form disubmit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function compare(Request $request)
    {
        // 1. Validasi Input tingkat lanjut untuk memastikan ID aman dan ada di database
        $request->validate([
            'country1' => 'required|exists:countries,id',
            'country2' => 'required|exists:countries,id',
        ]);

        // 2. Mengambil kembali list negara untuk dropdown dengan pengurutan alfabet
        $countries = Country::orderBy('country_name')->get();

        // 3. Mengambil data negara yang dipilih (Menggunakan findOrFail untuk keamanan ekstra)
        $country1 = Country::findOrFail($request->input('country1'));
        $country2 = Country::findOrFail($request->input('country2'));

        // 4. Menentukan rekomendasi berdasarkan Risk Index terendah
$winner = $country1->risk_index <= $country2->risk_index
    ? $country1
    : $country2;

        // 5. Mengirimkan semua data matang ke View
        return view('comparison.index', compact(
            'countries',
            'country1',
            'country2',
            'winner'
        ));
    }
}

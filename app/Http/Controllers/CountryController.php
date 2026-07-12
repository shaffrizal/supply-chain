<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Imports\CountriesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('country_name')->paginate(10);

        return view('countries.index', compact('countries'));
    }

    public function import()
    {
        return view('countries.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Country::truncate();

        Excel::import(
            new CountriesImport,
            $request->file('file')
        );

        return redirect()
            ->route('countries.index')
            ->with('success', 'Data negara berhasil diimport.');
    }

      public function importApi()
{
    $response = Http::get('https://restcountries.com/v3.1/all');

    dd($response->body());
}
    public function create()
    {
        return view('countries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_name' => 'required',
            'country_code' => 'required|unique:countries',
            'region' => 'required'
        ]);

        Country::create($request->all());

        return redirect()
            ->route('countries.index')
            ->with('success', 'Negara berhasil ditambahkan');
    }

   public function show(Country $country)
{
    // GDP
    $gdp = null;

    $response = Http::get(
        "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/NY.GDP.MKTP.CD?format=json"
    );

    $json = $response->json();

    if (isset($json[1])) {

        foreach ($json[1] as $item) {

            if (!is_null($item['value'])) {

                $gdp = $item['value'];

                break;

            }

        }

    }

    return view('countries.show', compact(
        'country',
        'gdp'
    ));
    }

    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }
    public function update(Request $request, Country $country)
    {
    $request->validate([
        'country_name' => 'required',
        'country_code' => 'required|unique:countries,country_code,' . $country->id,
        'region' => 'required',
        'capital' => 'nullable',
        'currency' => 'nullable',
    ]);

    $country->update([

        'country_name' => $request->country_name,
        'country_code' => $request->country_code,
        'region' => $request->region,
        'capital' => $request->capital,
        'currency' => $request->currency,

    ]);

    return redirect()
        ->route('countries.index')
        ->with('success','Data negara berhasil diubah.');
}

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()
            ->route('countries.index')
            ->with('success', 'Data berhasil dihapus');
    }
}
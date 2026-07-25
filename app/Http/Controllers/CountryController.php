<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountryController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Country List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $region = trim((string) $request->input('region'));
        $query = Country::query()
            ->when($search, fn ($q) => $q->where(function ($nested) use ($search) {
                $nested->where('country_name', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%")
                    ->orWhere('capital', 'like', "%{$search}%");
            }))
            ->when($region, fn ($q) => $q->where('region', $region));
        $countries = $query->orderBy('country_name')->paginate(15)->withQueryString();
        $regions = Country::whereNotNull('region')->distinct()->orderBy('region')->pluck('region');
        $stats = [
            'total' => Country::count(),
            'low' => Country::where('risk_index', '<', 40)->count(),
            'medium' => Country::whereBetween('risk_index', [40, 69])->count(),
            'high' => Country::where('risk_index', '>=', 70)->count(),
        ];

        return view(
            'countries.index',
            compact('countries', 'regions', 'stats', 'search', 'region')
        );
    }

        /*
    |--------------------------------------------------------------------------
    | Import Countries From REST Countries API
    |--------------------------------------------------------------------------
    */
public function importApi()
{
    try {
        $response = Http::connectTimeout(3)->timeout(15)->get('https://restcountries.com/v3.1/all', [
            'fields' => 'name,cca2,region,capital,currencies,population,latlng,flag',
        ]);
    } catch (\Throwable) {
        return redirect()->route('countries.index')->with('error', 'REST Countries API is temporarily unavailable. Existing data remains safe.');
    }
    if (! $response->successful()) {
        return redirect()->route('countries.index')->with('error', 'Country synchronization could not be completed. Please try again later.');
    }
    foreach ($response->json() as $item) {
        $currency = array_key_first($item['currencies'] ?? []);
        Country::updateOrCreate(['country_code' => $item['cca2']], [
            'country_name' => $item['name']['common'], 'region' => $item['region'] ?: null,
            'capital' => $item['capital'][0] ?? null, 'currency' => $currency,
            'population' => $item['population'] ?? null, 'flag' => $item['flag'] ?? null,
            'latitude' => $item['latlng'][0] ?? null, 'longitude' => $item['latlng'][1] ?? null,
        ]);
    }
    return redirect()->route('countries.index')->with('success', 'Data negara berhasil disinkronkan dari REST Countries.');
}
        /*
    |--------------------------------------------------------------------------
    | Create Country
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('countries.create');
    }

    /*
    |--------------------------------------------------------------------------
    | Store Country
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'country_name' => 'required|string|max:150',

            'country_code' => 'required|string|max:5|unique:countries',

            'region' => 'required|string|max:100',

            'capital' => 'nullable|string|max:100',

            'currency' => 'nullable|string|max:20',

            'population' => 'nullable|integer|min:0',

            'latitude' => 'nullable|numeric|between:-90,90',

            'longitude' => 'nullable|numeric|between:-180,180',

            'risk_index' => 'nullable|integer|between:0,100'

        ]);

      $index = (int) $request->input('risk_index', 30);

Country::create([

    'country_name' => $request->country_name,

    'country_code' => strtoupper($request->country_code),

    'region' => $request->region,

    'capital' => $request->capital,

    'currency' => $request->currency ? strtoupper($request->currency) : null,

    'population' => $request->population,

    'latitude' => $request->latitude,

    'longitude' => $request->longitude,

    'risk_index' => $index,

    'risk_level' => $index >= 70
        ? 'High'
        : ($index >= 40 ? 'Medium' : 'Low')

]);

        return redirect()

            ->route('countries.index')

            ->with(

                'success',

                'Country created successfully.'

            );
    }

    /*
    |--------------------------------------------------------------------------
    | Country Detail
    |--------------------------------------------------------------------------
    */

    public function show(Country $country)
    {

        $gdp = '-';

        $inflation = '-';

        $exchangeRate = '-';

        $weather = [];

        /*
        |--------------------------------------------------
        | GDP
        |--------------------------------------------------
        */

        try{

            $response = Http::connectTimeout(2)->timeout(6)->get(

                "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/NY.GDP.MKTP.CD?format=json"

            );

            $json = $response->json();

            if(isset($json[1])){

                foreach($json[1] as $item){

                    if($item['value'] != null){

                        $gdp = number_format($item['value']);

                        break;

                    }

                }

            }

        }catch(\Exception $e){

            $gdp='-';

        }

        /*
        |--------------------------------------------------
        | Inflation
        |--------------------------------------------------
        */

        try{

            $response = Http::connectTimeout(2)->timeout(6)->get(

                "https://api.worldbank.org/v2/country/{$country->country_code}/indicator/FP.CPI.TOTL.ZG?format=json"

            );

            $json = $response->json();

            if(isset($json[1])){

                foreach($json[1] as $item){

                    if($item['value'] != null){

                        $inflation = round($item['value'],2);

                        break;

                    }

                }

            }

        }catch(\Exception $e){

            $inflation='-';

        }

        /*
        |--------------------------------------------------
        | Weather
        |--------------------------------------------------
        */

        try{

            $weather = Http::connectTimeout(2)->timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'=>$country->latitude, 'longitude'=>$country->longitude,
                'current'=>'temperature_2m,relative_humidity_2m,wind_speed_10m,precipitation,weather_code',
            ])->json();

        }catch(\Exception $e){

            $weather=[];

        }

        /*
        |--------------------------------------------------
        | Exchange Rate
        |--------------------------------------------------
        */

        try{

            $exchange = Http::connectTimeout(2)->timeout(6)

            ->get(

                'https://open.er-api.com/v6/latest/USD'

            )->json();

            if(isset($exchange['rates'][$country->currency])){

                $exchangeRate = number_format(

                    $exchange['rates'][$country->currency],

                    2

                );

            }

        }catch(\Exception $e){

            $exchangeRate='-';

        }

        return view(

            'countries.show',

            compact(

                'country',

                'gdp',

                'inflation',

                'weather',

                'exchangeRate'

            )

        );

    }
        /*
    |--------------------------------------------------------------------------
    | Edit Country
    |--------------------------------------------------------------------------
    */

    public function edit(Country $country)
    {
        return view(
            'countries.edit',
            compact('country')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Country
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Country $country)
    {
        $request->validate([

            'country_name' =>
                'required|string|max:150',

            'country_code' =>
                'required|string|max:5|unique:countries,country_code,' . $country->id,

            'region' =>
                'required|string|max:100',

            'capital' =>
                'nullable|string|max:100',

            'currency' =>
                'nullable|string|max:20',

            'population' =>
                'nullable|integer|min:0',

            'latitude' =>
                'nullable|numeric|between:-90,90',

            'longitude' =>
                'nullable|numeric|between:-180,180'

        ]);

        $country->update([

            'country_name' => $request->country_name,

            'country_code' => strtoupper($request->country_code),

            'region' => $request->region,

            'capital' => $request->capital,

            'currency' => $request->currency ? strtoupper($request->currency) : null,

            'population' => $request->population,

            'latitude' => $request->latitude,

            'longitude' => $request->longitude

        ]);

        return redirect()

            ->route('countries.index')

            ->with(

                'success',

                'Country updated successfully.'

            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Country
    |--------------------------------------------------------------------------
    */

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()

            ->route('countries.index')

            ->with(

                'success',

                'Country deleted successfully.'

            );
    }

}

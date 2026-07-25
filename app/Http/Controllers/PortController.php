<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;
use App\Services\WeatherService;
use App\Services\CountryService;
use App\Services\RiskScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PortController extends Controller
{
    public function create(): View
    {
        return view('ports.create', ['port' => new Port()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $port = Port::create($this->validatedPort($request));

        return redirect()->route('admin.ports.index')
            ->with('success', 'Port facility has been added successfully.');
    }

    public function edit(Port $port): View
    {
        return view('ports.edit', compact('port'));
    }

    public function update(Request $request, Port $port): RedirectResponse
    {
        $port->update($this->validatedPort($request, $port));

        return redirect()->route('admin.ports.index')
            ->with('success', 'Port facility has been updated successfully.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        $port->delete();

        return redirect()->route('admin.ports.index')
            ->with('success', 'Port facility has been removed.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $ports = Port::query()

            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('port_name', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });

            })

            ->orderBy('country')
            ->orderBy('port_name')
            ->paginate(15)
            ->withQueryString();

        $totalPorts = Port::count();
        $activePorts = Port::where('status', 'Active')->count();
        $highRiskPorts = Port::where('risk_level', 'High')->count();
        $coveredCountries = Port::whereNotNull('country')->distinct('country')->count('country');

       return view('ports.index', compact(
    'ports',
    'totalPorts',
    'search',
    'activePorts',
    'highRiskPorts',
    'coveredCountries'
));
    }

public function show(
    Port $port,
    WeatherService $weatherService,
    CountryService $countryService,
    RiskScoreService $riskScoreService
)
{
    $weather = $weatherService->getWeather(
        $port->latitude,
        $port->longitude
    );

    $country = $countryService->getCountry(
        $port->country
    );

    $risk = $riskScoreService->calculate($weather);

    return view('ports.show', compact(
        'port',
        'weather',
        'country',
        'risk'
    ));
}

    private function validatedPort(Request $request, ?Port $port = null): array
    {
        $data = $request->validate([
            'port_code' => ['nullable', 'string', 'max:12', 'unique:ports,port_code,'.($port?->id ?? 'NULL')],
            'port_name' => ['required', 'string', 'max:150'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'port_type' => ['required', 'in:Seaport,Container Terminal,Harbor,Marina,River Port,Dry Port'],
            'annual_capacity' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:Active,Limited,Inactive'],
            'risk_index' => ['required', 'integer', 'between:0,100'],
        ]);

        $data['port_code'] = isset($data['port_code']) ? strtoupper($data['port_code']) : null;
        $data['country_code'] = isset($data['country_code']) ? strtoupper($data['country_code']) : null;
        $data['risk_level'] = $data['risk_index'] >= 70 ? 'High' : ($data['risk_index'] >= 40 ? 'Medium' : 'Low');

        return $data;
    }

}

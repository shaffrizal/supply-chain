<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function settings()
    {
        return view('admin.settings.index');
    }

    public function ports(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = in_array($request->input('status'), ['Active', 'Limited', 'Inactive'], true)
            ? $request->input('status') : '';
        $ports = Port::query()
            ->when($search, fn ($query) => $query->where(function ($nested) use ($search) {
                $nested->where('port_name', 'like', "%{$search}%")
                    ->orWhere('port_code', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('country')->orderBy('port_name')->paginate(25)->withQueryString();

        return view('admin.ports.index', [
            'ports' => $ports, 'search' => $search, 'status' => $status,
            'totalPorts' => Port::count(),
            'activePorts' => Port::where('status', 'Active')->count(),
            'limitedPorts' => Port::where('status', 'Limited')->count(),
            'highRiskPorts' => Port::where('risk_index', '>=', 70)->count(),
        ]);
    }
}

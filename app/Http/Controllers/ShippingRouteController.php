<?php

namespace App\Http\Controllers;

use App\Models\ShippingRoute;
use Illuminate\Support\Facades\DB;

class ShippingRouteController extends Controller
{
    public function index()
    {
        $routes = ShippingRoute::with(['origin', 'destination'])->latest('id')->paginate(25)->withQueryString();
        $routeStats = ShippingRoute::query()
            ->selectRaw('COUNT(*) AS total_routes')
            ->selectRaw("SUM(CASE WHEN route_status = 'Active' THEN 1 ELSE 0 END) AS active_routes")
            ->selectRaw('COALESCE(SUM(distance_km), 0) AS total_distance')
            ->selectRaw('COALESCE(AVG(estimated_days), 0) AS average_transit')
            ->selectRaw('COALESCE(MAX(distance_km), 0) AS longest_route')
            ->selectRaw('COALESCE(MIN(distance_km), 0) AS shortest_route')
            ->selectRaw('COALESCE(MIN(estimated_days), 0) AS fastest_transit')->first();
        $portUnion = DB::table('shipping_routes')->selectRaw('origin_port_id AS port_id')
            ->union(DB::table('shipping_routes')->selectRaw('destination_port_id AS port_id'));
        $connectedPorts = DB::query()->fromSub($portUnion, 'connected_ports')->distinct()->count('port_id');

        return view(
            'shipping_routes.index',
            compact('routes', 'routeStats', 'connectedPorts')
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Port;
use App\Models\ShippingRoute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShippingRouteSeeder extends Seeder
{
    private const TARGET = 12000;

    public function run(): void
    {
        $ports = Port::query()->whereNotNull('latitude')->whereNotNull('longitude')
            ->orderBy('id')->get(['id', 'latitude', 'longitude', 'status']);

        if ($ports->count() < self::TARGET) {
            throw new RuntimeException('Diperlukan minimal 12.000 pelabuhan berkoordinat untuk membangun jaringan rute.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        ShippingRoute::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $records = [];
        $count = $ports->count();

        foreach ($ports->take(self::TARGET)->values() as $index => $origin) {
            $destination = $ports[($index + 120) % $count];
            $distance = max(1, (int) round($this->haversine(
                (float) $origin->latitude, (float) $origin->longitude,
                (float) $destination->latitude, (float) $destination->longitude,
            )));
            $records[] = [
                'origin_port_id' => $origin->id,
                'destination_port_id' => $destination->id,
                'distance_km' => $distance,
                'estimated_days' => max(1, (int) ceil($distance / 650)),
                'route_status' => $origin->status === 'Active' && $destination->status === 'Active' ? 'Active' : 'Inactive',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($records) === 500) {
                DB::table('shipping_routes')->insert($records);
                $records = [];
            }
        }
        if ($records !== []) DB::table('shipping_routes')->insert($records);

        if (ShippingRoute::count() !== self::TARGET) {
            throw new RuntimeException('Proses seeding gagal menghasilkan tepat 12.000 rute pengiriman.');
        }
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latitudeDelta = deg2rad($lat2 - $lat1);
        $longitudeDelta = deg2rad($lon2 - $lon1);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($longitudeDelta / 2) ** 2;
        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

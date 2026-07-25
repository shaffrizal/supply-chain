<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $sourcePorts = $this->readCsv(database_path('data/world_ports.csv'));
        if (count($sourcePorts) !== 100) {
            throw new RuntimeException('Dataset world_ports.csv harus berisi tepat 100 pelabuhan induk.');
        }

        $target = 12000;
        $current = Port::count();
        if ($current > $target) {
            throw new RuntimeException("Jumlah pelabuhan saat ini {$current}, melebihi target {$target}.");
        }

        $countryCodes = Country::pluck('country_code', 'country_name')
            ->mapWithKeys(fn ($code, $name) => [mb_strtolower(trim($name)) => $code]);
        $now = now();
        $records = [];
        $existingNames = Port::pluck('port_name')->flip();
        $remaining = $target - $current;

        foreach ($sourcePorts as $sourceIndex => $source) {
            for ($terminal = 1; $terminal <= 120; $terminal++) {
                $seed = abs(crc32($source['port_name'].'-'.$terminal));
                $risk = 10 + ($seed % 81);
                $ring = intdiv(max(0, $terminal - 2), 12) + 1;
                $angle = deg2rad(($terminal * 137.508) % 360);
                $latitudeOffset = $terminal === 1 ? 0 : cos($angle) * $ring * 0.003;
                $longitudeOffset = $terminal === 1 ? 0 : sin($angle) * $ring * 0.003;
                $portName = $terminal === 1
                    ? $source['port_name']
                    : $source['port_name'].' - Terminal '.str_pad(
                        (string) $terminal,
                        $terminal <= 10 ? 2 : 3,
                        '0',
                        STR_PAD_LEFT
                    );

                if (isset($existingNames[$portName]) || $remaining <= 0) {
                    continue;
                }

                $records[] = [
                    'port_name' => $portName,
                    'country' => $source['country'],
                    'country_code' => $countryCodes[mb_strtolower(trim($source['country']))] ?? null,
                    'city' => $source['city'] ?: null,
                    'latitude' => is_numeric($source['latitude']) ? round((float) $source['latitude'] + $latitudeOffset, 7) : null,
                    'longitude' => is_numeric($source['longitude']) ? round((float) $source['longitude'] + $longitudeOffset, 7) : null,
                    'port_type' => $terminal === 1 ? ($source['port_type'] ?: 'Seaport') : 'Logistics Terminal',
                    'annual_capacity' => 500000 + ($seed % 24500000),
                    'status' => $source['status'] ?: 'Active',
                    'risk_index' => $risk,
                    'risk_level' => $risk >= 70 ? 'High' : ($risk >= 40 ? 'Medium' : 'Low'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $remaining--;
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('ports')->insert($chunk);
        }

        if (Port::count() !== $target) {
            throw new RuntimeException('Proses seeding gagal menghasilkan tepat 12.000 pelabuhan.');
        }
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $headers = fgetcsv($handle);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) === count($headers)) $rows[] = array_combine($headers, $values);
        }
        fclose($handle);
        return $rows;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([

            CountrySeeder::class,

            PortSeeder::class,

            ShippingRouteSeeder::class,

            AdminUserSeeder::class,

        ]);

        if (config('services.risk.refresh_after_seed')) {
            $this->command?->info('Refreshing weighted risk scores from live indicators...');
            Artisan::call('risk:update', [], $this->command?->getOutput());
        } else {
            $this->command?->warn(
                'Seed risk values are placeholders. Run "php artisan risk:update", '.
                'or set SEED_REFRESH_RISK_SCORES=true before seeding.'
            );
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('gdp_growth', 10, 4)->nullable()->after('inflation_rate');
            $table->decimal('trade_percentage', 10, 4)->nullable()->after('gdp_growth');
            $table->unsignedSmallInteger('population_data_year')->nullable()->after('economic_data_year');
            $table->unsignedSmallInteger('inflation_data_year')->nullable()->after('population_data_year');
            $table->unsignedSmallInteger('growth_data_year')->nullable()->after('inflation_data_year');
            $table->unsignedSmallInteger('trade_data_year')->nullable()->after('growth_data_year');
            $table->unsignedSmallInteger('exports_data_year')->nullable()->after('trade_data_year');
            $table->unsignedSmallInteger('imports_data_year')->nullable()->after('exports_data_year');
            $table->timestamp('world_bank_synced_at')->nullable()->after('imports_data_year');
        });
    }

    public function down(): void
    {
        Schema::table('countries', fn (Blueprint $table) => $table->dropColumn([
            'gdp_growth',
            'trade_percentage',
            'population_data_year',
            'inflation_data_year',
            'growth_data_year',
            'trade_data_year',
            'exports_data_year',
            'imports_data_year',
            'world_bank_synced_at',
        ]));
    }
};

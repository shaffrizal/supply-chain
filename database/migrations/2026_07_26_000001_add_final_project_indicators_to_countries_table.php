<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->json('languages')->nullable()->after('currency');
            $table->decimal('inflation_rate', 10, 4)->nullable()->after('gdp');
            $table->decimal('exports_value', 20, 2)->nullable()->after('inflation_rate');
            $table->decimal('imports_value', 20, 2)->nullable()->after('exports_value');
            $table->unsignedSmallInteger('economic_data_year')->nullable()->after('imports_value');
        });
    }

    public function down(): void
    {
        Schema::table('countries', fn (Blueprint $table) => $table->dropColumn([
            'languages',
            'inflation_rate',
            'exports_value',
            'imports_value',
            'economic_data_year',
        ]));
    }
};

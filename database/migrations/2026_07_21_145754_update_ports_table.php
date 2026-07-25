<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {

            $table->string('city')->nullable()->after('country_code');

            $table->decimal('latitude',10,7)->nullable();

            $table->decimal('longitude',10,7)->nullable();

            $table->string('port_type')->nullable();

            $table->bigInteger('annual_capacity')->nullable();

            $table->string('status')->default('Active');

            $table->integer('risk_index')->default(0);

            $table->string('risk_level')->default('Low');

        });
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {

            $table->dropColumn([
                'city',
                'latitude',
                'longitude',
                'port_type',
                'annual_capacity',
                'status',
                'risk_index',
                'risk_level'
            ]);

        });
    }
};
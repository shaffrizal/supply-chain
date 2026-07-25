<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {

            $table->id();

            // ISO Code
            $table->string('country_code', 5)->unique();

            // Country Name
            $table->string('country_name');

            // Emoji Flag
            $table->string('flag',20)->nullable();

            // Currency
            $table->string('currency',20)->nullable();

            // Exchange Rate terhadap USD
            $table->decimal('exchange_rate',15,4)->nullable();

            // GDP (Billion USD)
            $table->decimal('gdp',15,2)->nullable();

            // Population
            $table->bigInteger('population')->nullable();

            // Region
            $table->string('region')->nullable();

            // Capital
            $table->string('capital')->nullable();

            // Risk Index
            $table->integer('risk_index')->default(0);

            // Risk Level
            $table->enum('risk_level',[
                'Low',
                'Medium',
                'High'
            ])->default('Low');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
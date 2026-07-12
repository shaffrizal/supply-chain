<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {

            $table->id();

            $table->string('country_name');

            $table->string('country_code')->unique();

            $table->string('currency')->nullable();

            $table->string('region')->nullable();

            $table->string('capital')->nullable();

            $table->unsignedBigInteger('population')->nullable();

            $table->decimal('latitude',10,6)->nullable();

            $table->decimal('longitude',10,6)->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->decimal('weather_risk', 5, 2)->default(0);
            $table->decimal('inflation_risk', 5, 2)->default(0);
            $table->decimal('news_risk', 5, 2)->default(0);
            $table->decimal('currency_risk', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2);
            $table->string('risk_level', 10);
            $table->timestamps();
        });
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->unique();
            $table->string('sentiment', 10)->default('Neutral');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('news_cache');
        Schema::dropIfExists('risk_scores');
    }
};

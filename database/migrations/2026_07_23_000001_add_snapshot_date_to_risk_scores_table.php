<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->date('snapshot_date')->nullable()->after('country_id');
        });

        DB::statement('UPDATE risk_scores SET snapshot_date = DATE(created_at) WHERE snapshot_date IS NULL');

        Schema::table('risk_scores', function (Blueprint $table) {
            $table->unique(['country_id', 'snapshot_date'], 'risk_scores_country_snapshot_unique');
            $table->index('snapshot_date');
        });
    }

    public function down(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->dropUnique('risk_scores_country_snapshot_unique');
            $table->dropIndex(['snapshot_date']);
            $table->dropColumn('snapshot_date');
        });
    }
};

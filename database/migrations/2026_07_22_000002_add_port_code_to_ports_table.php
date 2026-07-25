<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('port_code', 12)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('ports', fn (Blueprint $table) => $table->dropUnique(['port_code']));
        Schema::table('ports', fn (Blueprint $table) => $table->dropColumn('port_code'));
    }
};

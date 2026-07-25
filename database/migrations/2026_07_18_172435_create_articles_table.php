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
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');            // Judul Berita/Brief
        $table->text('content');            // Isi Lengkap Berita
        $table->string('category');         // Kategori Risiko (Weather, Logistics, dll)
        $table->string('author')->nullable(); // Penulis berita
        $table->timestamps();               // created_at dan updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

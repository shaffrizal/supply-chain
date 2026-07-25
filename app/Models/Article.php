<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    // Nama tabel di database kamu
    protected $table = 'articles';

    // Membuka izin agar kolom-kolom ini bisa disimpan dan ditampilkan
    protected $fillable = [
        'title',
        'content',
        'category',
        'author'
    ];
}
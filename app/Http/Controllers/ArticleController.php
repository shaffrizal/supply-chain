<?php

namespace App\Http\Controllers;

use App\Models\Article; // Pastikan model Article di-import
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Menampilkan daftar artikel intelligence briefs.
     */
    public function index()
    {
        // Mengambil semua data artikel dari database
        $articles = Article::latest()->get();

        // Melempar variabel $articles ke dalam view admin
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Menyimpan artikel intel baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
        ]);

        Article::create([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'author' => auth()->user()->name ?? 'Risk Analyst', // Otomatis mengambil nama user login
        ]);

        return redirect()->route('admin.articles.index')
                         ->with('success', 'Intelligence brief successfully broadcasted.');
    }
}

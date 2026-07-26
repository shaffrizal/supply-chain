<?php

namespace App\Http\Controllers;

use App\Models\Article; // Pastikan model Article di-import
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Menampilkan daftar artikel intelligence briefs.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $category = trim((string) $request->input('category'));
        $articles = Article::query()
            ->when($search, fn ($query) => $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            }))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest()
            ->get();

        return view('admin.articles.index', [
            'articles' => $articles,
            'search' => $search,
            'categoryFilter' => $category,
            'totalArticles' => Article::count(),
            'categories' => Article::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'authors' => Article::whereNotNull('author')->distinct()->count('author'),
            'latestArticleAt' => Article::max('updated_at'),
        ]);
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

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string'],
        ]);
        $article->update([...$validated, 'author' => auth()->user()->name]);

        return back()->with('success', 'Intelligence brief successfully updated.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return back()->with('success', 'Intelligence brief successfully deleted.');
    }
}

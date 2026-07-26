@extends('layouts.bootstrap5')

@section('title', 'Supply Chain Risk Intelligence Articles')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="mb-1 text-dark font-weight-bold"><i class="fas fa-newspaper text-primary mr-2"></i> Risk Intelligence Briefs</h1>
        <small class="text-muted">Publish and manage global logistics disruptions, weather warning bulletins, and supply chain insights.</small>
    </div>
    <button class="btn btn-primary shadow-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalAddArticle">
        <i class="fas fa-feather-alt mr-1"></i> Compose New Brief
    </button>
</div>
@stop

@section('content')

{{-- Notifikasi Sukses/Gagal --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <h5><i class="icon fas fa-check-circle"></i> Insight Published!</h5>
        {{ session('success') }}
        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Ringkasan Statistik Artikel -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
            <span class="info-box-icon bg-primary shadow-sm"><i class="fas fa-file-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted font-weight-bold">Total Briefs</span>
                <span class="info-box-number text-dark h4 mb-0">{{ number_format($totalArticles) }} Reports</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
            <span class="info-box-icon bg-success shadow-sm"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted font-weight-bold">Categories</span>
                <span class="info-box-number text-dark h4 mb-0">{{ number_format($categories->count()) }} Topics</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
            <span class="info-box-icon bg-warning shadow-sm"><i class="fas fa-exclamation-triangle text-white"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted font-weight-bold">Contributors</span>
                <span class="info-box-number text-dark h4 mb-0">{{ number_format($authors) }} Authors</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border-0">
            <span class="info-box-icon bg-info shadow-sm"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted font-weight-bold">Last Updated</span>
                <span class="info-box-number text-dark font-weight-normal text-sm mb-0">{{ $latestArticleAt ? \Illuminate\Support\Carbon::parse($latestArticleAt)->diffForHumans() : 'No activity' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Utama Articles -->
<div class="card shadow border-0">
    <div class="card-header bg-white py-3 border-bottom border-light">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-dark font-weight-bold mb-0">
                <i class="fas fa-stream mr-1 text-secondary"></i> Intelligence Bulletin Board
            </h3>
            <form class="card-tools d-flex" method="GET">
                <div class="input-group input-group-sm" style="width: 270px;">
                    <input type="search" name="search" value="{{ $search }}" class="form-control float-right border-light" placeholder="Search title, content, author" autocomplete="off">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <select class="form-control form-control-sm ml-2" name="category" onchange="this.form.submit()"><option value="">All categories</option>@foreach($categories as $option)<option value="{{ $option }}" @selected($categoryFilter===$option)>{{ $option }}</option>@endforeach</select>
                @if($search||$categoryFilter)<a class="btn btn-default btn-sm ml-2" href="{{ route('admin.articles.index') }}"><i class="fas fa-times"></i></a>@endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="thead-light text-secondary">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="45%">Intel Brief Title & Summary</th>
                        <th width="15%">Risk Category</th>
                        <th width="12%">Author</th>
                        <th width="13%">Date Published</th>
                        <th width="10%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $index => $article)
                    <tr>
                        <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                        <td class="align-middle">
                            <div class="d-flex align-items-start">
                                <!-- Editorial Icon Badge -->
                                <div class="mr-3 bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm border" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="fas fa-shield-alt text-secondary"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold text-dark text-md mb-1">{{ $article->title }}</div>
                                    <p class="text-muted text-sm mb-0 text-truncate-custom" style="max-width: 500px;">
                                        {{ Str::limit(strip_tags($article->content), 90) }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            @if(str_contains(strtolower($article->category ?? 'logistics'), 'weather') || str_contains(strtolower($article->category ?? 'logistics'), 'cuaca'))
                                <span class="badge badge-warning text-white font-weight-bold px-2 py-1"><i class="fas fa-cloud-sun mr-1"></i> Weather Threat</span>
                            @elseif(str_contains(strtolower($article->category ?? 'logistics'), 'risk') || str_contains(strtolower($article->category ?? 'logistics'), 'bahaya'))
                                <span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i> Critical Risk</span>
                            @else
                                <span class="badge badge-info font-weight-bold px-2 py-1"><i class="fas fa-ship mr-1"></i> {{ $article->category ?? 'General Ops' }}</span>
                            @endif
                        </td>
                        <td class="align-middle text-dark font-weight-500">
                            <i class="fas fa-user-tie mr-1 text-muted text-xs"></i> {{ $article->author ?? 'Risk Analyst' }}
                        </td>
                        <td class="align-middle text-secondary font-mono text-sm">
                            {{ $article->created_at ? $article->created_at->format('M d, Y • H:i') : 'Jul 19, 2026' }}
                        </td>
                        <td class="text-center align-middle">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-xs btn-outline-info px-2" title="Edit Bulletin" data-bs-toggle="modal" data-bs-target="#editArticle{{ $article->id }}"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ route('admin.articles.destroy',$article) }}" onsubmit="return confirm('Delete this intelligence brief?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger px-2" title="Revoke Bulletin"><i class="fas fa-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-5">
                            <i class="fas fa-newspaper fa-3x mb-3 d-block text-secondary"></i>
                            <span class="h5 d-block font-weight-bold">No Operational Bulletins Found</span>
                            <small>No risk intelligence briefs have been published to the system database yet.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top py-3 clearfix">
        <small class="text-muted float-left font-weight-500">Showing {{ $articles->count() ?? 0 }} risk briefing assets.</small>
    </div>
</div>

<!-- MODAL COMPOSE NEW ARTICLE (FORM INTELLIGENCE BRIEF) -->
<div class="modal fade" id="modalAddArticle" tabindex="-1" role="dialog" aria-labelledby="modalAddArticleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalAddArticleLabel">
                    <i class="fas fa-feather-alt mr-2"></i> Compose Supply Chain Intelligence Brief
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.articles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title" class="font-weight-bold">Report Title</label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Typhoon Warning Affecting Shanghai Port Shipping Lanes" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category" class="font-weight-bold">Risk Classification</label>
                                <select name="category" id="category" class="form-control custom-select" required>
                                    <option value="Logistics Risk">Logistics Risk</option>
                                    <option value="Weather Threat">Weather Threat</option>
                                    <option value="Economic Blockade">Economic Blockade</option>
                                    <option value="General Operations">General Operations</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="content" class="font-weight-bold">Intelligence Summary & Analysis</label>
                                <textarea name="content" id="content" class="form-control" rows="6" placeholder="Provide full intelligence brief body, tactical updates, and countermeasure recommendations..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-paper-plane mr-1"></i> Broadcast Intelligence</button>
                </div>
            </form>
        </div>
    </div>
</div>
@foreach($articles as $article)
<div class="modal fade" id="editArticle{{ $article->id }}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('admin.articles.update',$article) }}">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title">Edit Intelligence Brief</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><div class="form-group"><label>Title</label><input class="form-control" name="title" value="{{ $article->title }}" required></div><div class="form-group"><label>Category</label><input class="form-control" name="category" value="{{ $article->category }}" required></div><div class="form-group"><label>Content</label><textarea class="form-control" name="content" rows="8" required>{{ $article->content }}</textarea></div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form></div></div>
@endforeach
@stop

@section('css')
<style>
    .font-mono {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    }
    .text-truncate-custom {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .font-weight-500 {
        font-weight: 500;
    }
</style>
@stop

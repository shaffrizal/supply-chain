@extends('layouts.bootstrap5')

@section('title', 'News Intelligence')

@section('content_header')
    <div class="sc-page-head news-page-head">
        <div>
            <span class="sc-eyebrow">GLOBAL MEDIA MONITORING</span>
            <h1>News Intelligence</h1>
            <p>Monitor isu rantai pasok, sumber berita, dan sentimen pasar dalam satu ruang kerja.</p>
        </div>
        <div class="news-live-badge">
            <span class="news-live-dot"></span>
            {{ $apiAvailable ? $providerName.' live' : 'Cached intelligence' }}
        </div>
    </div>
@stop

@section('content')
    <div class="news-shell pb-4">
        <section class="sc-card news-filter-card">
            <form method="GET" action="{{ route('news.index') }}" class="news-search-form">
                <input type="hidden" name="topic" value="{{ $topic }}">
                <div class="news-search-box">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" value="{{ $search }}"
                        placeholder="Cari isu, perusahaan, negara, atau komoditas..." aria-label="Cari berita">
                    @if ($search)
                        <a href="{{ route('news.index', ['topic' => $topic]) }}" class="news-clear" title="Hapus pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
                <button type="submit" class="sc-btn sc-btn-primary">
                    <i class="fas fa-chart-line"></i> Analisis Berita
                </button>
            </form>
            <div class="news-topics" aria-label="Topik berita">
                @foreach ($topics as $value => $label)
                    <a href="{{ route('news.index', ['topic' => $value]) }}"
                        class="news-topic {{ $topic === $value && !$search ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </section>

        <div class="news-kpi-grid">
            <article class="news-kpi news-kpi-primary">
                <span class="news-kpi-icon"><i class="far fa-newspaper"></i></span>
                <div><strong>{{ number_format($analyzedCount) }}</strong><span>Artikel dianalisis</span></div>
                <small>{{ number_format($providerTotal) }} hasil tersedia</small>
            </article>
            <article class="news-kpi news-kpi-positive">
                <span class="news-kpi-icon"><i class="fas fa-arrow-up"></i></span>
                <div><strong>{{ $sentimentPercentages['Positive'] }}%</strong><span>Sentimen positif</span></div>
                <small>{{ $sentimentCounts['Positive'] }} artikel</small>
            </article>
            <article class="news-kpi news-kpi-neutral">
                <span class="news-kpi-icon"><i class="fas fa-minus"></i></span>
                <div><strong>{{ $sentimentPercentages['Neutral'] }}%</strong><span>Sentimen netral</span></div>
                <small>{{ $sentimentCounts['Neutral'] }} artikel</small>
            </article>
            <article class="news-kpi news-kpi-negative">
                <span class="news-kpi-icon"><i class="fas fa-arrow-down"></i></span>
                <div><strong>{{ $sentimentPercentages['Negative'] }}%</strong><span>Sentimen negatif</span></div>
                <small>{{ $sentimentCounts['Negative'] }} artikel</small>
            </article>
        </div>

        <div class="row news-analysis-row">
            <div class="col-xl-4 mb-4">
                <section class="sc-card news-panel h-100">
                    <div class="news-panel-head">
                        <div><span class="sc-eyebrow">SENTIMENT MIX</span><h2>Market pulse</h2></div>
                        <span class="sentiment-badge sentiment-{{ strtolower($dominantSentiment) }}">{{ $dominantSentiment }}</span>
                    </div>
                    <div class="news-doughnut-wrap">
                        <canvas id="sentimentChart"></canvas>
                        <div class="news-doughnut-center"><strong>{{ $analyzedCount }}</strong><span>articles</span></div>
                    </div>
                    <div class="news-legend">
                        @foreach (['Positive' => '#22c55e', 'Neutral' => '#f59e0b', 'Negative' => '#ef4444'] as $label => $color)
                            <div><span style="background: {{ $color }}"></span>{{ $label }}<strong>{{ $sentimentCounts[$label] }}</strong></div>
                        @endforeach
                    </div>
                </section>
            </div>
            <div class="col-xl-5 mb-4">
                <section class="sc-card news-panel h-100">
                    <div class="news-panel-head">
                        <div><span class="sc-eyebrow">SOURCE COVERAGE</span><h2>Top media sources</h2></div>
                        <span class="news-source-count">{{ $topSources->count() }} sources</span>
                    </div>
                    @forelse ($topSources as $source => $count)
                        @php $width = $analyzedCount ? max(7, round(($count / $analyzedCount) * 100)) : 0; @endphp
                        <div class="news-source-row">
                            <div><span>{{ $loop->iteration }}</span><strong>{{ $source }}</strong><b>{{ $count }}</b></div>
                            <div class="news-source-track"><span style="width: {{ $width }}%"></span></div>
                        </div>
                    @empty
                        <div class="news-mini-empty">Belum ada sumber yang dapat dianalisis.</div>
                    @endforelse
                </section>
            </div>
            <div class="col-xl-3 mb-4">
                <section class="sc-card news-insight h-100">
                    <span class="news-insight-icon"><i class="fas fa-lightbulb"></i></span>
                    <span class="sc-eyebrow">INTELLIGENCE BRIEF</span>
                    <h2>{{ $dominantSentiment }} outlook</h2>
                    <p>
                        @if ($analyzedCount === 0)
                            Belum ada artikel untuk kata kunci ini. Coba pilih topik lain atau ubah pencarian.
                        @elseif ($dominantSentiment === 'Positive')
                            Pemberitaan saat ini lebih banyak menyoroti pertumbuhan, pemulihan, dan penguatan pasar.
                        @elseif ($dominantSentiment === 'Negative')
                            Pemberitaan menunjukkan peningkatan risiko. Periksa isu gangguan, konflik, dan keterlambatan.
                        @else
                            Pemberitaan relatif berimbang tanpa sinyal risiko atau peluang yang dominan.
                        @endif
                    </p>
                    <div class="news-insight-meta"><span>Query aktif</span><strong>{{ Str::limit($keyword, 34) }}</strong></div>
                    <small>Analisis berbasis kata kunci pada judul dan ringkasan berita.</small>
                </section>
            </div>
        </div>

        <section class="news-feed-section">
            <div class="news-feed-head">
                <div><span class="sc-eyebrow">LATEST COVERAGE</span><h2>Berita terbaru</h2></div>
                <p>Menampilkan {{ $analyzedCount }} artikel teratas untuk “{{ $keyword }}”</p>
            </div>

            <div class="news-grid">
                @forelse ($articles as $article)
                    @php
                        $publishedAt = filled($article['publishedAt'] ?? null)
                            ? \Illuminate\Support\Carbon::parse($article['publishedAt'])
                            : null;
                        $source = data_get($article, 'source.name', 'Unknown source');
                        $sentiment = $article['sentiment'] ?? 'Neutral';
                    @endphp
                    <article class="sc-card news-article-card">
                        <a href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer" class="news-article-media">
                            @if (filled($article['urlToImage'] ?? null))
                                <img src="{{ $article['urlToImage'] }}" alt="" loading="lazy"
                                    onerror="this.parentElement.classList.add('image-error'); this.remove();">
                            @endif
                            <span class="news-image-fallback"><i class="far fa-newspaper"></i></span>
                            <span class="sentiment-badge sentiment-{{ strtolower($sentiment) }}">{{ $sentiment }}</span>
                        </a>
                        <div class="news-article-body">
                            <div class="news-article-meta">
                                <span><i class="far fa-building"></i> {{ Str::limit($source, 28) }}</span>
                                <span><i class="far fa-clock"></i> {{ $publishedAt ? $publishedAt->diffForHumans() : 'Baru saja' }}</span>
                            </div>
                            <h3><a href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer">{{ $article['title'] }}</a></h3>
                            <p>{{ Str::limit($article['description'] ?? 'Buka artikel untuk membaca informasi selengkapnya.', 145) }}</p>
                            <a href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer" class="news-read-more">
                                Baca artikel <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="sc-card news-empty-state">
                        <span><i class="far fa-folder-open"></i></span>
                        <h3>Berita belum ditemukan</h3>
                        <p>Coba gunakan kata kunci yang lebih luas atau pilih salah satu topik yang tersedia.</p>
                        <a href="{{ route('news.index') }}" class="sc-btn sc-btn-primary">Kembali ke Supply Chain</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}">
    <style>
        .news-shell{--ink:#10233f;--muted:#718096;--line:#e6edf6}.news-page-head{align-items:center}.news-live-badge{display:flex;align-items:center;gap:9px;padding:10px 14px;border:1px solid #dbe7f4;border-radius:999px;background:#fff;color:#52657d;font-size:12px;font-weight:700}.news-live-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 5px rgba(34,197,94,.12)}
        .news-filter-card{padding:18px;margin-bottom:20px}.news-search-form{display:flex;gap:12px}.news-search-box{height:48px;display:flex;align-items:center;gap:12px;flex:1;padding:0 16px;border:1px solid #dbe4ef;border-radius:12px;background:#f8fafc;transition:.2s}.news-search-box:focus-within{background:#fff;border-color:#3485ff;box-shadow:0 0 0 3px rgba(52,133,255,.1)}.news-search-box>i{color:#8ca0b8}.news-search-box input{width:100%;border:0;outline:0;background:transparent;color:var(--ink)}.news-clear{color:#94a3b8}.news-topics{display:flex;flex-wrap:wrap;gap:8px;margin-top:15px}.news-topic{padding:7px 12px;border:1px solid #e0e8f2;border-radius:999px;color:#64748b;font-size:12px;font-weight:700;background:#fff}.news-topic:hover,.news-topic.active{background:#eaf3ff;border-color:#b9d6ff;color:#1673ea;text-decoration:none}
        .news-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}.news-kpi{position:relative;display:grid;grid-template-columns:48px 1fr;align-items:center;gap:12px;padding:18px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(30,64,108,.06);overflow:hidden}.news-kpi:before{content:"";position:absolute;inset:0 auto 0 0;width:3px;background:var(--accent)}.news-kpi-icon{display:grid;place-items:center;width:46px;height:46px;border-radius:13px;color:var(--accent);background:var(--soft);font-size:19px}.news-kpi strong{display:block;color:var(--ink);font-size:25px;line-height:1}.news-kpi div span{display:block;margin-top:6px;color:#607087;font-size:12px;font-weight:700}.news-kpi small{grid-column:2;color:#94a3b8;font-size:10px;margin-top:-7px}.news-kpi-primary{--accent:#1677ff;--soft:#eaf3ff}.news-kpi-positive{--accent:#22c55e;--soft:#eafaf0}.news-kpi-neutral{--accent:#f59e0b;--soft:#fff7df}.news-kpi-negative{--accent:#ef4444;--soft:#fff0f0}
        .news-analysis-row{margin-left:-8px;margin-right:-8px}.news-analysis-row>[class*=col-]{padding-left:8px;padding-right:8px}.news-panel,.news-insight{padding:20px}.news-panel-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px}.news-panel-head h2,.news-insight h2,.news-feed-head h2{margin:3px 0 0;color:var(--ink);font-size:18px;font-weight:800}.news-source-count{color:#7d8ba1;font-size:11px;font-weight:700}.sentiment-badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800}.sentiment-positive{background:#e9f9ef;color:#159447}.sentiment-neutral{background:#fff6dc;color:#b77900}.sentiment-negative{background:#ffeded;color:#dc3030}.news-doughnut-wrap{position:relative;width:190px;height:190px;margin:4px auto 14px}.news-doughnut-center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;pointer-events:none}.news-doughnut-center strong{font-size:28px;color:var(--ink)}.news-doughnut-center span{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8}.news-legend{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.news-legend div{display:grid;grid-template-columns:7px 1fr auto;align-items:center;gap:6px;color:#7b8a9d;font-size:10px}.news-legend div>span{width:7px;height:7px;border-radius:50%}.news-legend strong{color:var(--ink)}
        .news-source-row{margin-bottom:15px}.news-source-row>div:first-child{display:grid;grid-template-columns:24px 1fr auto;align-items:center;gap:8px;margin-bottom:7px}.news-source-row>div:first-child span{display:grid;place-items:center;width:22px;height:22px;border-radius:7px;background:#eef5ff;color:#2579e9;font-size:10px;font-weight:800}.news-source-row strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#43546b;font-size:12px}.news-source-row b{color:#8290a3;font-size:11px}.news-source-track{height:5px;margin-left:32px;border-radius:5px;background:#eef2f7;overflow:hidden}.news-source-track span{display:block;height:100%;border-radius:5px;background:linear-gradient(90deg,#1976ed,#67a8ff)}.news-mini-empty{padding:55px 10px;text-align:center;color:#8492a6}.news-insight{position:relative;color:#dceaff;background:linear-gradient(145deg,#0a2953,#104c8a);border:0}.news-insight .sc-eyebrow{color:#79baff}.news-insight h2{margin:8px 0 14px;color:#fff;font-size:22px}.news-insight p{font-size:12px;line-height:1.7;color:#c6d9ee}.news-insight-icon{display:grid;place-items:center;width:42px;height:42px;margin-bottom:22px;border-radius:12px;background:rgba(255,255,255,.12);color:#ffd25c}.news-insight-meta{margin-top:24px;padding-top:16px;border-top:1px solid rgba(255,255,255,.14)}.news-insight-meta span,.news-insight-meta strong{display:block}.news-insight-meta span{font-size:10px;color:#91b7dd;text-transform:uppercase;letter-spacing:1px}.news-insight-meta strong{margin-top:5px;color:#fff;font-size:13px}.news-insight>small{display:block;margin-top:18px;color:#86a9cd;font-size:9px;line-height:1.5}
        .news-feed-section{margin-top:2px}.news-feed-head{display:flex;align-items:end;justify-content:space-between;margin-bottom:15px;padding:0 2px}.news-feed-head p{margin:0;color:#8090a5;font-size:11px}.news-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.news-article-card{overflow:hidden;transition:transform .2s,box-shadow .2s}.news-article-card:hover{transform:translateY(-3px);box-shadow:0 14px 32px rgba(32,65,106,.12)}.news-article-media{position:relative;display:block;height:176px;background:linear-gradient(135deg,#dbeafe,#eff6ff);overflow:hidden}.news-article-media img{position:relative;z-index:1;width:100%;height:100%;object-fit:cover;transition:transform .35s}.news-article-card:hover img{transform:scale(1.035)}.news-image-fallback{position:absolute;inset:0;display:grid;place-items:center;color:#91b9e8;font-size:34px}.news-article-media .sentiment-badge{position:absolute;z-index:2;top:12px;left:12px;box-shadow:0 3px 12px rgba(15,35,63,.1)}.news-article-body{padding:17px}.news-article-meta{display:flex;justify-content:space-between;gap:10px;color:#8190a3;font-size:10px}.news-article-meta span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.news-article-body h3{margin:12px 0 9px;font-size:15px;font-weight:800;line-height:1.4}.news-article-body h3 a{color:var(--ink)}.news-article-body h3 a:hover{color:#1673ea;text-decoration:none}.news-article-body p{min-height:51px;margin:0 0 15px;color:#718096;font-size:11px;line-height:1.55}.news-read-more{display:flex;align-items:center;justify-content:space-between;padding-top:13px;border-top:1px solid #edf1f6;color:#2478e5;font-size:11px;font-weight:800}.news-read-more:hover{text-decoration:none}.news-empty-state{grid-column:1/-1;padding:65px 20px;text-align:center}.news-empty-state>span{display:grid;place-items:center;width:64px;height:64px;margin:0 auto 16px;border-radius:18px;background:#edf5ff;color:#3683e9;font-size:24px}.news-empty-state h3{font-size:18px;color:var(--ink)}.news-empty-state p{color:#7d8ba0}
        @media(max-width:1199px){.news-kpi-grid{grid-template-columns:repeat(2,1fr)}.news-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.news-live-badge{display:none}.news-search-form{flex-direction:column}.news-search-form .sc-btn{justify-content:center}.news-kpi-grid,.news-grid{grid-template-columns:1fr}.news-feed-head{display:block}.news-feed-head p{margin-top:6px}.news-topics{flex-wrap:nowrap;overflow-x:auto;padding-bottom:4px}.news-topic{white-space:nowrap}.news-article-media{height:200px}}
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const sentimentCanvas = document.getElementById('sentimentChart');
        if (sentimentCanvas && window.Chart) {
            new Chart(sentimentCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Positive', 'Neutral', 'Negative'],
                    datasets: [{
                        data: @json(array_values($sentimentCounts)),
                        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { displayColors: false } }
                }
            });
        }
    </script>
@stop

@extends('layouts.app')

@section('page_title', 'Favorite Monitoring')

@section('content_header')
<div class="watch-hero">
    <div class="watch-hero-copy">
        <span class="watch-eyebrow"><i class="fas fa-satellite-dish"></i> PERSONAL RISK WORKSPACE</span>
        <h1>Favorite Monitoring</h1>
        <p>Pantau negara prioritas, perubahan eksposur, dan sinyal risiko rantai pasok dalam satu workspace.</p>
    </div>
    <div class="watch-hero-actions">
        <a href="{{ route('countries.index') }}" class="watch-btn watch-btn-primary"><i class="fas fa-plus"></i> Add Country</a>
        <a href="{{ route('comparison.index') }}" class="watch-btn watch-btn-secondary"><i class="fas fa-balance-scale"></i> Compare Countries</a>
    </div>
</div>
@endsection

@section('content')
<div class="watch-workspace">
    @if(session('success'))
        <div class="watch-alert"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span><button data-bs-dismiss="alert" aria-label="Close">&times;</button></div>
    @endif

    <section class="watch-stats" aria-label="Watchlist summary">
        @foreach([
            ['icon' => 'fa-star', 'tone' => 'blue', 'label' => 'Monitored Countries', 'value' => $stats['total'], 'note' => 'Countries in your watchlist'],
            ['icon' => 'fa-exclamation-triangle', 'tone' => 'red', 'label' => 'High Risk Alerts', 'value' => $stats['high'], 'note' => 'Require immediate attention'],
            ['icon' => 'fa-chart-line', 'tone' => 'amber', 'label' => 'Average Risk', 'value' => $stats['average'] ?: '—', 'note' => 'Composite score out of 100'],
            ['icon' => 'fa-globe-asia', 'tone' => 'violet', 'label' => 'Regions Covered', 'value' => $stats['regions'], 'note' => 'Geographic exposure'],
        ] as $stat)
        <article class="watch-stat {{ $stat['tone'] }}">
            <span class="watch-stat-icon"><i class="fas {{ $stat['icon'] }}"></i></span>
            <div><small>{{ $stat['label'] }}</small><strong>{{ $stat['value'] }}</strong><p>{{ $stat['note'] }}</p></div>
            <i class="fas {{ $stat['icon'] }} watch-stat-watermark"></i>
        </article>
        @endforeach
    </section>

    @if($watchlists->isEmpty())
        <section class="watch-panel empty-watchlist">
            <div class="empty-radar"><i class="fas fa-star"></i></div>
            <span class="watch-eyebrow">MONITORING READY</span>
            <h2>Build your priority watchlist</h2>
            <p>Tambahkan negara yang penting bagi operasi Anda untuk memantau risk score, indikator ekonomi, dan paparan logistiknya.</p>
            <a href="{{ route('countries.index') }}" class="watch-btn watch-btn-primary"><i class="fas fa-globe"></i> Browse Countries</a>
        </section>
    @else
        <section class="watch-panel active-watchlist">
            <header class="watch-panel-head">
                <div><span class="watch-eyebrow">ACTIVE WATCHLIST</span><h2>Monitored Countries</h2><p>Prioritas yang saat ini berada dalam pengawasan Anda.</p></div>
                <span class="watch-count"><i class="fas fa-satellite-dish"></i> {{ $watchlists->count() }} monitored</span>
            </header>
            <div class="watch-grid">
                @foreach($watchlists as $item)
                    @php
                        $country = $item->country;
                        $tone = $country->risk_level === 'High' ? 'high' : ($country->risk_level === 'Medium' ? 'medium' : 'low');
                    @endphp
                    <article class="country-watch-card {{ $tone }}" data-watch-card>
                        <div class="country-card-glow"></div>
                        <header class="country-card-head">
                            <div class="country-identity">
                                <span class="country-flag"><img src="https://flagcdn.com/w80/{{ strtolower($country->country_code) }}.png" alt="{{ $country->country_name }} flag"></span>
                                <div><span>{{ $country->country_code }}</span><h3>{{ $country->country_name }}</h3><p><i class="fas fa-map-marker-alt"></i> {{ $country->capital ?: 'Capital unavailable' }}</p></div>
                            </div>
                            <form method="POST" action="{{ route('watchlists.destroy', $item) }}" data-watch-remove>
                                @csrf @method('DELETE')
                                <button class="watch-remove" title="Remove {{ $country->country_name }} from watchlist" aria-label="Remove {{ $country->country_name }}"><i class="fas fa-star"></i></button>
                            </form>
                        </header>

                        <div class="country-meta">
                            <div><i class="fas fa-globe"></i><span>Region<strong>{{ $country->region ?: '—' }}</strong></span></div>
                            <div><i class="fas fa-coins"></i><span>Currency<strong>{{ $country->currency ?: '—' }}</strong></span></div>
                            <div><i class="fas fa-users"></i><span>Population<strong>{{ $country->population ? number_format($country->population) : '—' }}</strong></span></div>
                        </div>

                        <div class="country-risk">
                            <div class="risk-heading"><span>SUPPLY CHAIN RISK</span><strong>{{ $country->risk_index }}<small>/100</small></strong></div>
                            <div class="risk-track"><i style="width:{{ min(100, $country->risk_index) }}%"></i></div>
                            <div class="risk-footer"><span class="risk-pill"><i></i>{{ $country->risk_level }} Risk</span><small>Updated {{ $country->updated_at?->diffForHumans() ?? 'recently' }}</small></div>
                        </div>

                        <a class="profile-link" href="{{ route('countries.show', $country) }}"><span>Open Intelligence Profile</span><i class="fas fa-arrow-right"></i></a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="watch-panel recommendation-panel">
        <header class="watch-panel-head">
            <div><span class="watch-eyebrow">RISK-BASED SUGGESTIONS</span><h2>Recommended for Monitoring</h2><p>Negara dengan risk score tinggi yang belum ada di watchlist Anda.</p></div>
            <a class="view-all-link" href="{{ route('countries.index') }}">Explore directory <i class="fas fa-arrow-right"></i></a>
        </header>
        <div class="recommendation-grid">
            @forelse($recommendations as $country)
                @php $tone = $country->risk_level === 'High' ? 'high' : ($country->risk_level === 'Medium' ? 'medium' : 'low'); @endphp
                <article class="recommend-card {{ $tone }}">
                    <div class="recommend-country">
                        <img src="https://flagcdn.com/w80/{{ strtolower($country->country_code) }}.png" alt="{{ $country->country_name }} flag">
                        <div><small>{{ $country->country_code }} · {{ $country->region ?: 'Global' }}</small><strong>{{ $country->country_name }}</strong></div>
                        <span>{{ $country->risk_index }}</span>
                    </div>
                    <div class="recommend-actions">
                        <span class="risk-pill"><i></i>{{ $country->risk_level }} Risk</span>
                        <form method="POST" action="{{ route('watchlists.store', $country) }}" data-watch-add>
                            @csrf
                            <button><i class="far fa-star"></i> Monitor</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="recommend-empty"><i class="fas fa-check-circle"></i><span>Semua rekomendasi prioritas sudah dipantau.</span></div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
.watch-workspace{--watch-panel:#0a1b2d;--watch-panel-2:#0d2238;--watch-line:#1b3c57;--watch-text:#e9f3fc;--watch-muted:#718ba4;--watch-blue:#2b8cff;padding-bottom:12px}
.watch-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:8px 0 10px}
.watch-hero:after{content:"";position:absolute;right:15%;bottom:-11px;width:280px;height:1px;background:linear-gradient(90deg,transparent,rgba(43,140,255,.55),transparent)}
.watch-eyebrow{display:block;color:#3696ff;font-size:9px;font-weight:850;letter-spacing:1.4px}.watch-eyebrow i{margin-right:6px}
.watch-hero h1{margin:7px 0 4px!important;color:#f4f8fc!important;font-size:28px!important;line-height:1.05;letter-spacing:-.6px}
.watch-hero p{max-width:650px;margin:0;color:#7894ad!important;font-size:12px;line-height:1.55}
.watch-hero-actions{display:flex;gap:9px}.watch-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:0 15px;border:1px solid transparent;border-radius:10px;color:#fff;font-size:11px;font-weight:800;text-decoration:none;transition:.2s}
.watch-btn:hover{transform:translateY(-2px);color:#fff;text-decoration:none}.watch-btn-primary{border-color:#3b97ff;background:linear-gradient(135deg,#2d91ff,#1466d8);box-shadow:0 9px 25px rgba(27,119,226,.25)}.watch-btn-secondary{border-color:#254b6b;background:#0b2034;color:#b4cce1}
.watch-alert{display:flex;align-items:center;gap:9px;margin-bottom:14px;padding:11px 14px;border:1px solid #176344;border-radius:10px;background:#092b21;color:#6be2a5;font-size:11px}.watch-alert button{margin-left:auto;border:0;background:transparent;color:#81ad99;font-size:18px}
.watch-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:16px}
.watch-stat{--tone:#2b8cff;--soft:rgba(43,140,255,.12);position:relative;display:flex;align-items:center;min-height:105px;padding:17px;overflow:hidden;border:1px solid #1b3a55!important;border-radius:14px;background:linear-gradient(145deg,#0d2238,#081827)!important;box-shadow:0 13px 32px rgba(0,0,0,.16)!important;transition:.22s}
.watch-stat:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--tone) 55%,#1b3a55)!important}.watch-stat.red{--tone:#ff6371;--soft:rgba(255,99,113,.12)}.watch-stat.amber{--tone:#ffb43b;--soft:rgba(255,180,59,.12)}.watch-stat.violet{--tone:#9b7cff;--soft:rgba(155,124,255,.12)}
.watch-stat-icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;margin-right:13px;border:1px solid color-mix(in srgb,var(--tone) 33%,transparent);border-radius:13px;background:var(--soft);color:var(--tone);font-size:17px}
.watch-stat small,.watch-stat strong,.watch-stat p{display:block;margin:0}.watch-stat small{color:#7690a9;font-size:9px;font-weight:700}.watch-stat strong{margin:3px 0;color:#f1f7fd;font-size:25px;line-height:1}.watch-stat p{color:#4f6d87;font-size:8px}.watch-stat-watermark{position:absolute;right:-8px;bottom:-12px;color:var(--tone);font-size:58px;opacity:.035;transform:rotate(-10deg)}
.watch-panel{margin-bottom:16px;overflow:hidden;border:1px solid var(--watch-line)!important;border-radius:15px;background:linear-gradient(145deg,rgba(12,31,50,.98),rgba(7,21,35,.98))!important;box-shadow:0 15px 40px rgba(0,0,0,.2)!important}
.watch-panel-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 19px;border-bottom:1px solid #18364f;background:linear-gradient(90deg,rgba(17,45,71,.58),rgba(8,24,40,.15))}
.watch-panel-head h2{margin:4px 0 2px!important;color:var(--watch-text)!important;font-size:16px!important}.watch-panel-head p{margin:0;color:#68839c!important;font-size:9px}
.watch-count{display:flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid #24537a;border-radius:999px;background:#0d2d49;color:#72b8ff;font-size:9px;font-weight:800}
.watch-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;padding:16px}
.country-watch-card{--risk:#24cb78;position:relative;min-width:0;padding:15px;overflow:hidden;border:1px solid #1c3d59;border-radius:13px;background:linear-gradient(155deg,#0f2942 0,#091b2d 58%,#071624 100%);box-shadow:0 10px 26px rgba(0,0,0,.18);transition:.22s}
.country-watch-card.medium{--risk:#f5b928}.country-watch-card.high{--risk:#ff5967}.country-watch-card:hover{transform:translateY(-3px);border-color:#2c648e;box-shadow:0 17px 34px rgba(0,0,0,.26)}
.country-card-glow{position:absolute;top:-75px;right:-70px;width:160px;height:160px;border-radius:50%;background:var(--risk);opacity:.055;filter:blur(3px)}
.country-card-head{position:relative;display:flex;align-items:flex-start;justify-content:space-between}.country-identity{display:flex;align-items:center;min-width:0}.country-flag{width:48px;height:36px;flex:0 0 48px;margin-right:10px;overflow:hidden;border:1px solid #31536e;border-radius:8px;background:#102a42;box-shadow:0 5px 13px rgba(0,0,0,.25)}.country-flag img{width:100%;height:100%;object-fit:cover}
.country-identity>div{min-width:0}.country-identity>div>span{color:#4989bd;font-size:7px;font-weight:850;letter-spacing:1.2px}.country-identity h3{overflow:hidden;margin:1px 0!important;color:#eff7ff!important;font-size:13px!important;text-overflow:ellipsis;white-space:nowrap}.country-identity p{margin:1px 0 0!important;color:#69849e!important;font-size:8px}.country-identity p i{margin-right:4px;color:#427ba8}
.watch-remove{display:grid;place-items:center;width:31px;height:31px;border:1px solid #6d5518;border-radius:8px;background:#30280e;color:#ffc43d;cursor:pointer;transition:.2s}.watch-remove:hover{border-color:#b98b20;background:#453812;color:#ffe08a;transform:rotate(-8deg)}
.country-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin:14px 0}.country-meta>div{display:flex;align-items:center;gap:7px;min-width:0;padding:9px 8px;border:1px solid #17354e;border-radius:8px;background:rgba(6,20,34,.72)}.country-meta>div>i{color:#428ed0;font-size:9px}.country-meta span,.country-meta strong{display:block;min-width:0}.country-meta span{color:#58758f;font-size:6px;text-transform:uppercase}.country-meta strong{overflow:hidden;margin-top:2px;color:#bcd0e2;font-size:8px;text-overflow:ellipsis;white-space:nowrap}
.country-risk{padding:11px;border:1px solid #193951;border-radius:10px;background:rgba(5,18,30,.58)}.risk-heading,.risk-footer{display:flex;align-items:center;justify-content:space-between}.risk-heading span{color:#6d8ba5;font-size:7px;font-weight:800;letter-spacing:.7px}.risk-heading strong{color:var(--risk);font-size:16px}.risk-heading strong small{color:#56718a;font-size:7px}
.risk-track{height:6px;margin:7px 0;border-radius:20px;background:#132d42;overflow:hidden}.risk-track i{display:block;height:100%;border-radius:20px;background:linear-gradient(90deg,color-mix(in srgb,var(--risk) 72%,#1684ff),var(--risk));box-shadow:0 0 12px color-mix(in srgb,var(--risk) 50%,transparent)}
.risk-pill{display:inline-flex;align-items:center;gap:5px;color:var(--risk);font-size:8px;font-weight:800}.risk-pill i{width:6px;height:6px;border-radius:50%;background:var(--risk);box-shadow:0 0 0 3px color-mix(in srgb,var(--risk) 14%,transparent)}.risk-footer small{color:#49657d;font-size:7px}
.profile-link{display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding:9px 10px;border:1px solid #1d4c75;border-radius:8px;background:linear-gradient(90deg,#0c3152,#0b263f);color:#79bdff;font-size:9px;font-weight:800;text-decoration:none}.profile-link:hover{border-color:#3289d5;background:#103b61;color:#c1e1ff;text-decoration:none}.profile-link i{transition:.2s}.profile-link:hover i{transform:translateX(3px)}
.view-all-link{display:flex;align-items:center;gap:7px;color:#65adf5;font-size:9px;font-weight:800}.view-all-link:hover{color:#b6dbff;text-decoration:none}
.recommendation-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:15px}.recommend-card{--risk:#24cb78;padding:12px;border:1px solid #193b57;border-radius:11px;background:linear-gradient(145deg,#0d243a,#081827);transition:.2s}.recommend-card.medium{--risk:#f5b928}.recommend-card.high{--risk:#ff5967}.recommend-card:hover{transform:translateY(-2px);border-color:#2b618b}
.recommend-country{display:grid;grid-template-columns:40px minmax(0,1fr) auto;align-items:center;gap:9px}.recommend-country img{width:40px;height:29px;border:1px solid #2a4a65;border-radius:7px;object-fit:cover}.recommend-country small,.recommend-country strong{display:block}.recommend-country small{overflow:hidden;color:#587691;font-size:7px;text-overflow:ellipsis;white-space:nowrap}.recommend-country strong{overflow:hidden;margin-top:2px;color:#deebf7;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.recommend-country>span{display:grid;place-items:center;width:31px;height:31px;border:1px solid color-mix(in srgb,var(--risk) 32%,transparent);border-radius:50%;background:color-mix(in srgb,var(--risk) 10%,transparent);color:var(--risk);font-size:10px;font-weight:900}
.recommend-actions{display:flex;align-items:center;justify-content:space-between;margin-top:11px;padding-top:10px;border-top:1px solid #17334b}.recommend-actions form{margin:0}.recommend-actions button{padding:6px 9px;border:1px solid #2373b5;border-radius:7px;background:#0d4775;color:#d6ecff;font-size:8px;font-weight:800;cursor:pointer}.recommend-actions button:hover{background:#12619d}.recommend-actions button:disabled{opacity:.65;cursor:wait}
.recommend-empty{grid-column:1/-1;display:flex;align-items:center;justify-content:center;gap:8px;padding:25px;color:#6e8ba4;font-size:10px}.recommend-empty i{color:#31ce7d}
.empty-watchlist{text-align:center;padding:42px 20px}.empty-watchlist h2{margin:7px 0 5px!important;color:#e8f3fc!important;font-size:18px!important}.empty-watchlist p{max-width:520px;margin:0 auto 18px;color:#718ba4!important;font-size:10px;line-height:1.7}.empty-radar{position:relative;display:grid;place-items:center;width:82px;height:82px;margin:0 auto 17px;border:1px solid #23547c;border-radius:50%;background:radial-gradient(circle,#123b5e 0,#0a2035 50%,#071725 70%);color:#55aaff;font-size:20px;box-shadow:0 0 35px rgba(44,140,255,.12)}.empty-radar:before,.empty-radar:after{content:"";position:absolute;border:1px solid rgba(64,153,232,.25);border-radius:50%}.empty-radar:before{inset:13px}.empty-radar:after{inset:27px}
@media(max-width:1200px){.watch-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.recommendation-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:900px){.watch-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.watch-hero{align-items:flex-start;flex-direction:column}.watch-hero-actions{width:100%}.watch-hero-actions .watch-btn{flex:1}}
@media(max-width:680px){.watch-grid,.recommendation-grid{grid-template-columns:1fr}.watch-panel-head{align-items:flex-start;flex-direction:column}.watch-count,.view-all-link{align-self:flex-start}.country-meta{grid-template-columns:1fr}.country-meta>div{padding:8px}.watch-hero h1{font-size:24px!important}}
@media(max-width:480px){.watch-stats{grid-template-columns:1fr}.watch-hero-actions{flex-direction:column}.watch-stat{min-height:92px}.watch-panel-head{padding:15px}.watch-grid{padding:11px}.country-watch-card{padding:12px}}
</style>
@endpush

@push('scripts')
<script>
const watchToast=message=>{const toast=document.createElement('div');toast.className='watch-alert';toast.innerHTML='<i class="fas fa-check-circle"></i><span>'+message+'</span>';document.querySelector('.watch-workspace')?.prepend(toast);setTimeout(()=>toast.remove(),3000)};
document.querySelectorAll('[data-watch-add]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();const button=form.querySelector('button'),original=button.innerHTML;button.disabled=true;button.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving';try{const response=await axios.post(form.action,{}, {headers:{'X-CSRF-TOKEN':@json(csrf_token()),Accept:'application/json'}});button.innerHTML='<i class="fas fa-check"></i> Monitoring';watchToast(response.data.message)}catch(error){button.disabled=false;button.innerHTML=original;alert(error.response?.data?.message||'Unable to update watchlist.')}});
document.querySelectorAll('[data-watch-remove]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();if(!confirm('Remove this country from monitoring?'))return;const button=form.querySelector('button');button.disabled=true;try{const response=await axios.delete(form.action,{headers:{'X-CSRF-TOKEN':@json(csrf_token()),Accept:'application/json'}});const card=form.closest('[data-watch-card]');card.style.opacity='0';card.style.transform='scale(.97)';setTimeout(()=>card.remove(),220);watchToast(response.data.message)}catch(error){button.disabled=false;alert(error.response?.data?.message||'Unable to update watchlist.')}}));
</script>
@endpush

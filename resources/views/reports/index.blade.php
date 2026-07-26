@extends('layouts.bootstrap5')
@section('title','Report Center')

@section('content_header')
<div class="report-hero"><div><span>EXECUTIVE REPORTING</span><h1>Intelligence Report Center</h1><p>Buat laporan operasional, analitik, dan administratif dalam format siap cetak.</p></div><div class="report-status"><i class="fas fa-print"></i><span>PRINT ENGINE<small>Browser Save as PDF ready</small></span></div></div>
@stop

@section('content')
<div class="report-shell">
    <section class="report-kpis">
        @foreach([['Countries',$stats['countries'],'globe','blue'],['Port Facilities',$stats['ports'],'anchor','cyan'],['High Risk',$stats['highRisk'],'exclamation-triangle','red'],['News Records',$stats['news'],'newspaper','amber'],['Report Types',$stats['reports'],'file-alt','violet']] as [$label,$value,$icon,$tone])
        <article class="{{ $tone }}"><i class="fas fa-{{ $icon }}"></i><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong><span>Available for reporting</span></div></article>
        @endforeach
    </section>

    <section class="report-panel report-builder">
        <header><div><span>REPORT BUILDER</span><h2>Configure a printable report</h2><p>Filter bersifat opsional. Hasil akan dibuka pada tab khusus cetak.</p></div><i class="fas fa-sliders-h"></i></header>
        <form id="reportBuilder" method="GET" target="_blank" data-print="{{ url('/reports/print') }}" data-export="{{ url('/reports/export') }}">
            <div><label>Report Type</label><select id="reportType">@foreach([
                'executive'=>'Executive Summary','risk'=>'Global Risk','economy'=>'Global Economy','ports'=>'Port Dataset','news'=>'News Sentiment','watchlist'=>'Priority Watchlist'
            ] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach @can('admin')<option value="articles">Intelligence Articles</option><option value="users">User Access</option>@endcan</select></div>
            <div><label>Search</label><input type="search" name="search" placeholder="Country, port, article..." autocomplete="off"></div>
            <div><label>Region</label><select name="region"><option value="">All regions</option>@foreach($regions as $region)<option>{{ $region }}</option>@endforeach</select></div>
            <div><label>Risk Level</label><select name="level"><option value="">All levels</option><option>High</option><option>Medium</option><option>Low</option></select></div>
            <div><label>Port Status</label><select name="status"><option value="">All statuses</option><option>Active</option><option>Limited</option><option>Inactive</option></select></div>
            <button name="output" value="print"><i class="fas fa-print"></i><span>Generate Report<small>Open print preview</small></span></button>
            <button class="csv-button" name="output" value="csv"><i class="fas fa-file-csv"></i><span>Export CSV<small>Excel-compatible</small></span></button>
        </form>
    </section>

    <section class="report-panel report-library">
        <header><div><span>REPORT LIBRARY</span><h2>Available intelligence reports</h2><p>Pilih laporan cepat atau gunakan Report Builder untuk menerapkan filter.</p></div></header>
        <div class="report-grid">
            @foreach([
                ['executive','Executive Summary','Strategic overview of countries, ports, routes, risks, and media intelligence.','fa-chart-pie','blue'],
                ['risk','Global Risk Report','Weighted country exposure with risk classification and measurement status.','fa-shield-alt','red'],
                ['economy','Global Economy','GDP, population, inflation, growth, trade, exports, and imports.','fa-chart-line','violet'],
                ['ports','Port Dataset','Port facilities, operational status, capacity, location, and risk exposure.','fa-anchor','cyan'],
                ['news','News Sentiment','Media coverage with positive, neutral, and negative sentiment classification.','fa-newspaper','amber'],
                ['watchlist','Priority Watchlist','Countries personally monitored for supply-chain exposure.','fa-star','green'],
            ] as [$type,$title,$description,$icon,$tone])
            <article class="{{ $tone }}"><span><i class="fas {{ $icon }}"></i></span><div><small>{{ strtoupper($type) }} REPORT</small><h3>{{ $title }}</h3><p>{{ $description }}</p></div><a target="_blank" href="{{ route('reports.print',$type) }}">Preview & Print <i class="fas fa-arrow-right"></i></a></article>
            @endforeach
            @can('admin')
            <article class="blue"><span><i class="fas fa-file-signature"></i></span><div><small>ADMIN REPORT</small><h3>Intelligence Articles</h3><p>Published briefs, authors, categories, and publication dates.</p></div><a target="_blank" href="{{ route('reports.print','articles') }}">Preview & Print <i class="fas fa-arrow-right"></i></a></article>
            <article class="red"><span><i class="fas fa-users-cog"></i></span><div><small>RESTRICTED REPORT</small><h3>User Access</h3><p>Authorized accounts, roles, departments, and registration records.</p></div><a target="_blank" href="{{ route('reports.print','users') }}">Preview & Print <i class="fas fa-arrow-right"></i></a></article>
            @endcan
        </div>
    </section>
</div>
@stop

@section('css')
<style>
.report-shell{--rp:#0a1c2e;--line:#1b3b56;--text:#eaf4fc;--muted:#718aa4;padding-bottom:20px}.report-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:7px 0 10px}.report-hero>div:first-child>span,.report-panel header span{color:#3596ff;font-size:9px;font-weight:850;letter-spacing:1.3px}.report-hero h1{margin:6px 0 4px!important;color:#f1f7fd!important;font-size:28px!important}.report-hero p{margin:0;color:#7791aa!important;font-size:11px}.report-status{display:flex;align-items:center;gap:9px;padding:10px 13px;border:1px solid #245277;border-radius:11px;background:#0b2237;color:#5caeff}.report-status span,.report-status small{display:block}.report-status span{font-size:7px;font-weight:850;letter-spacing:.7px}.report-status small{margin-top:2px;color:#7998b1;font-size:8px;letter-spacing:0}.report-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:11px;margin-bottom:15px}.report-kpis article{--tone:#2d91ff;display:flex;align-items:center;gap:11px;min-height:90px;padding:14px;border:1px solid var(--line);border-radius:13px;background:linear-gradient(145deg,#0d2339,#081827)}.report-kpis article.cyan{--tone:#38c6dd}.report-kpis article.red{--tone:#ff5967}.report-kpis article.amber{--tone:#f5b928}.report-kpis article.violet{--tone:#9979ff}.report-kpis article>i{display:grid;place-items:center;width:43px;height:43px;border-radius:11px;background:color-mix(in srgb,var(--tone) 12%,transparent);color:var(--tone)}.report-kpis small,.report-kpis strong,.report-kpis span{display:block}.report-kpis small{color:#718ba4;font-size:7px}.report-kpis strong{margin:3px 0;color:#eef6fc;font-size:19px}.report-kpis span{color:#4e6b84;font-size:6px}.report-panel{margin-bottom:15px;overflow:hidden;border:1px solid var(--line);border-radius:14px;background:linear-gradient(145deg,#0c2034,#071725);box-shadow:0 14px 35px rgba(0,0,0,.2)}.report-panel>header{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid #19364f;background:linear-gradient(90deg,#102d48,#091c2e)}.report-panel header h2{margin:3px 0!important;color:#eaf3fb!important;font-size:15px!important}.report-panel header p{margin:2px 0 0;color:#67829a!important;font-size:8px}.report-panel>header>i{color:#529bda}.report-builder form{display:grid;grid-template-columns:1.15fr 1.3fr 1fr .8fr .8fr auto;align-items:end;gap:9px;padding:15px}.report-builder form>div label{display:block;margin-bottom:5px;color:#6f8ba4;font-size:7px;font-weight:850;letter-spacing:.55px}.report-builder input,.report-builder select{width:100%;height:40px;padding:0 10px;border:1px solid #28516e;border-radius:9px;background:#061827;color:#bed1e0;font-size:9px}.report-builder button{display:flex;align-items:center;gap:8px;height:40px;padding:0 13px;border:1px solid #3c99ff;border-radius:9px;background:linear-gradient(135deg,#2d91ff,#1466d8);color:#fff;cursor:pointer}.report-builder button span,.report-builder button small{display:block;text-align:left}.report-builder button span{font-size:8px;font-weight:850}.report-builder button small{font-size:6px;font-weight:400;opacity:.7}.report-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;padding:15px}.report-grid article{--tone:#2d91ff;display:grid;grid-template-columns:43px 1fr;gap:10px;padding:14px;border:1px solid #1a3b56;border-radius:12px;background:#0a1e31;transition:.2s}.report-grid article.red{--tone:#ff5967}.report-grid article.violet{--tone:#9979ff}.report-grid article.cyan{--tone:#38c6dd}.report-grid article.amber{--tone:#f5b928}.report-grid article.green{--tone:#31ce7d}.report-grid article:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--tone) 55%,#1a3b56)}.report-grid article>span{display:grid;place-items:center;width:43px;height:43px;border-radius:11px;background:color-mix(in srgb,var(--tone) 12%,transparent);color:var(--tone)}.report-grid small{color:var(--tone);font-size:6px;font-weight:900;letter-spacing:.6px}.report-grid h3{margin:3px 0!important;color:#e2edf6!important;font-size:11px!important}.report-grid p{min-height:39px;margin:0;color:#657f97!important;font-size:7px;line-height:1.55}.report-grid a{grid-column:1/-1;display:flex;justify-content:space-between;padding-top:9px;border-top:1px solid #18354d;color:#5daaf0;font-size:7px;font-weight:850;text-decoration:none}@media(max-width:1200px){.report-builder form{grid-template-columns:repeat(3,1fr)}.report-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:800px){.report-kpis{grid-template-columns:repeat(2,1fr)}.report-grid{grid-template-columns:repeat(2,1fr)}.report-hero{align-items:flex-start;flex-direction:column}}@media(max-width:550px){.report-builder form,.report-grid,.report-kpis{grid-template-columns:1fr}}
.report-builder button.csv-button{border-color:#21b978;background:linear-gradient(135deg,#15935f,#087149)}
</style>
@stop

@section('js')
<script>document.getElementById('reportBuilder')?.addEventListener('submit',function(event){const output=event.submitter?.value||'print';this.action=(output==='csv'?this.dataset.export:this.dataset.print)+'/'+document.getElementById('reportType').value});</script>
@stop

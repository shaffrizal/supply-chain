@extends('layouts.bootstrap5')

@section('title', 'Global Risk Intelligence')

@section('content_header')
<div class="sc-page-head risk-page-head">
    <div>
        <span class="sc-eyebrow">SUPPLY CHAIN ANALYTICS</span>
        <h1>Global Risk Intelligence</h1>
        <p>Prioritaskan negara berdasarkan paparan cuaca, ekonomi, mata uang, dan sentimen berita.</p>
    </div>
    <div class="risk-update"><span><i class="fas fa-database"></i></span><div><small>DATA TERAKHIR</small><strong>{{ $lastUpdated ? \Illuminate\Support\Carbon::parse($lastUpdated)->format('d M Y, H:i') : 'Belum tersedia' }}</strong></div></div>
</div>
@stop

@section('content')
<div class="risk-shell pb-4">
    <div class="risk-kpis">
        <article class="risk-kpi primary"><span><i class="fas fa-globe-americas"></i></span><div><small>NEGARA DIPANTAU</small><strong>{{ number_format($totalCountries) }}</strong><p>Cakupan global</p></div></article>
        <article class="risk-kpi info"><span><i class="fas fa-chart-line"></i></span><div><small>RATA-RATA RISIKO</small><strong>{{ number_format($averageRisk, 1) }}</strong><p>Dari skala 0–100</p></div></article>
        <article class="risk-kpi danger"><span><i class="fas fa-exclamation-triangle"></i></span><div><small>RISIKO TINGGI</small><strong>{{ number_format($riskCounts['High']) }}</strong><p>Perlu perhatian</p></div></article>
        <article class="risk-kpi success"><span><i class="fas fa-shield-alt"></i></span><div><small>RISIKO RENDAH</small><strong>{{ number_format($riskCounts['Low']) }}</strong><p>Relatif stabil</p></div></article>
    </div>

    <div class="row risk-overview-row">
        <div class="col-xl-5 mb-4">
            <section class="sc-card risk-panel h-100">
                <div class="risk-panel-head"><div><span class="sc-eyebrow">GLOBAL DISTRIBUTION</span><h2>Risk composition</h2></div><span class="risk-scale">0—100 SCORE</span></div>
                <div class="risk-chart-area"><canvas id="riskDistributionChart"></canvas></div>
                <div class="risk-chart-legend">
                    <span><i class="low"></i> Low <b>{{ $riskCounts['Low'] }}</b></span>
                    <span><i class="medium"></i> Medium <b>{{ $riskCounts['Medium'] }}</b></span>
                    <span><i class="high"></i> High <b>{{ $riskCounts['High'] }}</b></span>
                </div>
            </section>
        </div>
        <div class="col-xl-7 mb-4">
            <section class="sc-card risk-panel h-100">
                <div class="risk-panel-head"><div><span class="sc-eyebrow">PRIORITY WATCH</span><h2>Highest-risk countries</h2></div><i class="fas fa-chart-line risk-head-icon"></i></div>
                <div class="top-risk-list">
                    @forelse($topRiskCountries as $country)
                    @php $tone = $country->risk_index >= 70 ? 'high' : ($country->risk_index >= 40 ? 'medium' : 'low'); @endphp
                    <div class="top-risk-item">
                        <span class="risk-rank">{{ $loop->iteration }}</span>
                        <img src="https://flagcdn.com/w40/{{ strtolower($country->country_code) }}.png" alt="" loading="lazy">
                        <div class="risk-country"><strong>{{ $country->country_name }}</strong><small>{{ $country->country_code }}</small></div>
                        <div class="risk-progress"><span><i class="{{ $tone }}" style="width:{{ min(100, $country->risk_index) }}%"></i></span></div>
                        <strong class="risk-number">{{ $country->risk_index }}</strong>
                        <em class="risk-status {{ $tone }}">{{ ucfirst($tone) }}</em>
                    </div>
                    @empty
                    <div class="risk-empty-mini">Belum ada data risiko.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="sc-card risk-trend-panel">
        <div class="risk-panel-head">
            <div><span class="sc-eyebrow">HISTORICAL RISK TREND</span><h2>{{ $trendCountry?->country_name ?? 'Global average' }} · 90 days</h2><p>Calculated from persisted weighted risk-score snapshots, never generated sample data.</p></div>
            <form method="GET" class="risk-trend-filter"><label>Scope</label><select name="country" onchange="this.form.submit()"><option value="">Global average</option>@foreach($trendCountries as $item)<option value="{{ $item->country_code }}" @selected($trendCountry?->country_code===$item->country_code)>{{ $item->country_name }}</option>@endforeach</select></form>
        </div>
        <div class="risk-trend-chart">@if(count($riskTrend)>0)<canvas id="riskTrendChart"></canvas>@else<div class="risk-trend-empty"><i class="fas fa-history"></i><strong>No risk snapshot available yet</strong><span>The first real observation will appear after <code>php artisan risk:update</code> finishes.</span></div>@endif</div>
    </section>

    <section class="sc-card risk-table-card">
        <div class="risk-table-toolbar">
            <div><span class="sc-eyebrow">COUNTRY DIRECTORY</span><h2>Risk assessment</h2><p>{{ $countries->total() }} hasil ditemukan</p></div>
            <form method="GET" action="{{ route('risk.index') }}" class="risk-filter-form">
                <div class="risk-search"><i class="fas fa-search"></i><input type="search" name="search" value="{{ $search }}" placeholder="Cari negara, kode, atau region" autocomplete="off" data-lpignore="true"></div>
                <select name="level" onchange="this.form.submit()" aria-label="Filter level risiko">
                    <option value="">Semua level</option>
                    @foreach(['High'=>'High risk','Medium'=>'Medium risk','Low'=>'Low risk'] as $value=>$label)<option value="{{ $value }}" @selected($level===$value)>{{ $label }}</option>@endforeach
                </select>
                <button class="sc-btn sc-btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                @if($search || $level)<a class="risk-reset" href="{{ route('risk.index') }}" title="Reset"><i class="fas fa-times"></i></a>@endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="risk-table">
                <thead><tr><th>Country</th><th>Region</th><th>Risk score</th><th>Risk drivers</th><th>Status</th><th>Updated</th></tr></thead>
                <tbody>
                @forelse($countries as $row)
                    @php $tone = strtolower($row['level']); @endphp
                    <tr>
                        <td><div class="country-cell"><img src="https://flagcdn.com/w40/{{ strtolower($row['country']->country_code) }}.png" alt=""><div><strong>{{ $row['country']->country_name }}</strong><small>{{ $row['country']->country_code }}</small></div></div></td>
                        <td><span class="region-label">{{ $row['country']->region ?: 'Global' }}</span></td>
                        <td><div class="score-cell"><strong>{{ number_format($row['score'],1) }}</strong><span><i class="{{ $tone }}" style="width:{{ min(100,$row['score']) }}%"></i></span></div></td>
                        <td><div class="driver-list"><span title="Weather"><i class="fas fa-cloud-sun"></i>{{ number_format($row['weather']) }}</span><span title="Inflation"><i class="fas fa-percentage"></i>{{ number_format($row['inflation']) }}</span><span title="News"><i class="far fa-newspaper"></i>{{ number_format($row['news']) }}</span><span title="Currency"><i class="fas fa-dollar-sign"></i>{{ number_format($row['currency']) }}</span>@if($row['is_estimated'])<em class="estimate-badge" title="Belum ada snapshot risk:update">Estimasi</em>@else<em class="observed-badge" title="Berasal dari snapshot indikator">Terukur</em>@endif</div></td>
                        <td><span class="risk-status {{ $tone }}"><i></i>{{ $row['level'] }}</span></td>
                        <td><span class="updated-cell">{{ $row['updated_at'] ? $row['updated_at']->diffForHumans() : '—' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="risk-empty"><i class="fas fa-search"></i><strong>Data tidak ditemukan</strong><span>Coba ubah pencarian atau filter risiko.</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($countries->hasPages())<div class="risk-pagination">{{ $countries->links('pagination::bootstrap-5') }}</div>@endif
    </section>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}">
<style>
.risk-shell{--ink:#10233f;--muted:#718096;--line:#e5edf6}.risk-page-head{align-items:center}.risk-update{display:flex;align-items:center;gap:11px;padding:10px 14px;border:1px solid #e1eaf4;border-radius:13px;background:#fff}.risk-update>span{display:grid;place-items:center;width:36px;height:36px;border-radius:10px;background:#eaf3ff;color:#2178e8}.risk-update small,.risk-update strong{display:block}.risk-update small{font-size:8px;letter-spacing:1px;color:#94a3b8}.risk-update strong{margin-top:3px;color:#42536a;font-size:11px}.risk-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}.risk-kpi{display:flex;align-items:center;gap:14px;padding:18px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(30,64,108,.06)}.risk-kpi>span{display:grid;place-items:center;flex:0 0 48px;height:48px;border-radius:14px;color:var(--accent);background:var(--soft);font-size:18px}.risk-kpi small,.risk-kpi strong,.risk-kpi p{display:block;margin:0}.risk-kpi small{color:#8492a6;font-size:9px;letter-spacing:.7px;font-weight:800}.risk-kpi strong{margin:3px 0;color:var(--ink);font-size:25px;line-height:1}.risk-kpi p{color:#9aa7b8;font-size:10px}.risk-kpi.primary{--accent:#1677ff;--soft:#eaf3ff}.risk-kpi.info{--accent:#7957df;--soft:#f1edff}.risk-kpi.danger{--accent:#ef4444;--soft:#fff0f0}.risk-kpi.success{--accent:#20ad60;--soft:#eaf9f1}.risk-overview-row{margin-left:-8px;margin-right:-8px}.risk-overview-row>[class*=col-]{padding:0 8px}.risk-panel{padding:20px}.risk-panel-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}.risk-panel-head h2,.risk-table-toolbar h2{margin:3px 0 0;color:var(--ink);font-size:18px;font-weight:800}.risk-scale{color:#91a0b3;font-size:9px;font-weight:800;letter-spacing:.7px}.risk-head-icon{color:#ef6670}.risk-chart-area{height:178px}.risk-chart-legend{display:flex;justify-content:center;gap:25px;margin-top:9px}.risk-chart-legend span{color:#7b899d;font-size:10px}.risk-chart-legend i{display:inline-block;width:7px;height:7px;margin-right:5px;border-radius:50%}.risk-chart-legend b{margin-left:4px;color:var(--ink)}i.low{background:#22c55e!important}i.medium{background:#f59e0b!important}i.high{background:#ef4444!important}.top-risk-list{display:grid;gap:5px}.top-risk-item{display:grid;grid-template-columns:25px 30px minmax(100px,1.2fr) minmax(80px,1fr) 38px 62px;align-items:center;gap:9px;padding:8px 5px;border-bottom:1px solid #edf1f6}.top-risk-item:last-child{border:0}.risk-rank{color:#a0abba;font-size:10px;text-align:center}.top-risk-item img,.country-cell img{width:27px;height:19px;border-radius:4px;object-fit:cover;box-shadow:0 1px 4px rgba(20,40,70,.15)}.risk-country strong,.risk-country small{display:block}.risk-country strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#35465d;font-size:11px}.risk-country small{color:#a0abba;font-size:8px}.risk-progress>span,.score-cell>span{display:block;height:5px;border-radius:8px;background:#edf2f7;overflow:hidden}.risk-progress i,.score-cell i{display:block;height:100%;border-radius:8px}.risk-number{color:var(--ink);font-size:11px;text-align:right}.risk-status{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:9px;font-style:normal;font-weight:800}.risk-status.low{color:#14964a;background:#e9f9ef}.risk-status.medium{color:#ad7100;background:#fff5d8}.risk-status.high{color:#dc3434;background:#ffeded}.risk-status>i{width:5px;height:5px;border-radius:50%;background:currentColor}.risk-empty-mini{padding:60px;text-align:center;color:#8b98a9}.risk-table-card{overflow:hidden}.risk-table-toolbar{display:flex;align-items:center;justify-content:space-between;padding:19px 20px;border-bottom:1px solid var(--line)}.risk-table-toolbar p{display:inline;margin-left:8px;color:#94a3b8;font-size:10px}.risk-filter-form{display:flex;gap:8px}.risk-search{display:flex;align-items:center;gap:8px;width:250px;height:39px;padding:0 12px;border:1px solid #dce5ef;border-radius:10px;background:#f8fafc}.risk-search i{color:#93a2b5;font-size:11px}.risk-search input{width:100%;border:0;outline:0;background:transparent;font-size:11px}.risk-filter-form select{height:39px;padding:0 30px 0 11px;border:1px solid #dce5ef;border-radius:10px;background:#fff;color:#53647a;font-size:11px}.risk-filter-form .sc-btn{height:39px}.risk-reset{display:grid;place-items:center;width:39px;border:1px solid #e1e8f1;border-radius:10px;color:#8997aa}.risk-table{width:100%;border-collapse:collapse}.risk-table th{padding:12px 16px;border-bottom:1px solid #e3eaf3;background:#f8fafc;color:#8090a4;font-size:9px;letter-spacing:.7px;text-transform:uppercase}.risk-table td{padding:12px 16px;border-bottom:1px solid #edf1f6;vertical-align:middle;color:#56667b;font-size:11px}.risk-table tbody tr:hover{background:#fbfdff}.country-cell{display:flex;align-items:center;gap:10px}.country-cell strong,.country-cell small{display:block}.country-cell strong{color:#24374f;font-size:11px}.country-cell small{color:#9aa7b8;font-size:8px}.region-label,.updated-cell{color:#77869a;font-size:10px}.score-cell{display:grid;grid-template-columns:35px minmax(75px,1fr);align-items:center;gap:9px}.score-cell strong{color:#21364f;font-size:12px}.driver-list{display:flex;gap:6px}.driver-list span{display:flex;align-items:center;gap:4px;padding:4px 6px;border-radius:6px;background:#f4f7fb;color:#64748b;font-size:9px}.driver-list i{color:#3a82e8}.risk-empty{display:flex;align-items:center;flex-direction:column;gap:8px;padding:45px;color:#8b98a9}.risk-empty>i{font-size:23px;color:#a8bdd5}.risk-empty strong{color:#53647a}.risk-pagination{display:flex;justify-content:flex-end;padding:15px 18px}.risk-pagination .pagination{margin:0}.risk-pagination .page-link{border-color:#e1e8f1;color:#58708e;font-size:10px}.risk-pagination .page-item.active .page-link{background:#1677ff;border-color:#1677ff}
@media(max-width:1199px){.risk-kpis{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.risk-update{display:none}.risk-kpis{grid-template-columns:1fr 1fr}.risk-table-toolbar{align-items:flex-start;flex-direction:column;gap:14px}.risk-filter-form{width:100%;flex-wrap:wrap}.risk-search{width:100%}.risk-filter-form select{flex:1}.top-risk-item{grid-template-columns:22px 28px 1fr 34px 58px}.risk-progress{display:none}.risk-table{min-width:870px}}@media(max-width:480px){.risk-kpis{grid-template-columns:1fr}}
.risk-trend-panel{margin-bottom:20px;padding:20px}.risk-trend-panel .risk-panel-head p{margin:4px 0 0;color:#94a3b8;font-size:9px}.risk-trend-chart{height:245px}.risk-trend-filter{display:flex;align-items:center;gap:8px}.risk-trend-filter label{margin:0;color:#94a3b8;font-size:9px}.risk-trend-filter select{height:36px;min-width:190px;border:1px solid #dce5ef;border-radius:9px;background:#fff;padding:0 10px;color:#53647a;font-size:10px}.risk-trend-empty{display:flex;align-items:center;justify-content:center;flex-direction:column;height:100%;border:1px dashed #dce5ef;border-radius:12px;background:#fafcff;color:#94a3b8}.risk-trend-empty i{margin-bottom:8px;color:#9bbcef;font-size:25px}.risk-trend-empty strong{color:#53647a;font-size:11px}.risk-trend-empty span{margin-top:4px;font-size:9px}.risk-trend-empty code{color:#1677ff}@media(max-width:767px){.risk-trend-panel .risk-panel-head{gap:12px;flex-direction:column}.risk-trend-filter{width:100%}.risk-trend-filter select{flex:1}}
</style>
<style>
.driver-list{align-items:center;flex-wrap:wrap}
.driver-list em{padding:4px 7px;border-radius:999px;font-size:8px;font-style:normal;font-weight:800}
.driver-list .estimate-badge{color:#9a6700;background:#fff4ce}
.driver-list .observed-badge{color:#16804b;background:#e8f8ef}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const riskCanvas=document.getElementById('riskDistributionChart');if(riskCanvas&&window.Chart){new Chart(riskCanvas,{type:'doughnut',data:{labels:['Low','Medium','High'],datasets:[{data:@json([$riskCounts['Low'],$riskCounts['Medium'],$riskCounts['High']]),backgroundColor:['#22c55e','#f59e0b','#ef4444'],borderWidth:0,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{display:false},tooltip:{displayColors:false}}}})}
const riskTrend=@json($riskTrend),riskTrendCanvas=document.getElementById('riskTrendChart');if(riskTrendCanvas&&riskTrend.length>0){new Chart(riskTrendCanvas,{type:'line',data:{labels:riskTrend.map(p=>p.date),datasets:[{data:riskTrend.map(p=>p.value),borderColor:'#9979ff',backgroundColor:'rgba(153,121,255,.12)',borderWidth:2.3,fill:true,tension:.28,pointRadius:riskTrend.length===1?5:2,pointHoverRadius:5,pointBackgroundColor:'#081827',pointBorderColor:'#9979ff',pointBorderWidth:2}]},options:{maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},plugins:{legend:{display:false},tooltip:{displayColors:false,backgroundColor:'#071827',borderColor:'#294f6c',borderWidth:1,titleColor:'#7e9bb4',bodyColor:'#e8f2fa',callbacks:{label:c=>'Risk score: '+Number(c.raw).toFixed(1),afterLabel:c=>'Snapshots: '+riskTrend[c.dataIndex].samples}}},scales:{x:{border:{display:false},grid:{display:false},ticks:{color:'#5e7b94',font:{size:9},maxTicksLimit:9,callback:(v,i)=>riskTrend[i].date.slice(5)}},y:{suggestedMin:0,suggestedMax:100,position:'right',border:{display:false},grid:{color:'rgba(95,130,158,.16)'},ticks:{color:'#5e7b94',font:{size:8},callback:v=>v+'/100'}}}}})}
</script>
@stop

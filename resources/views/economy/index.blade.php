@extends('layouts.bootstrap5')

@section('title', 'Global Economy Intelligence')

@section('content_header')
<div class="eco-hero">
    <div>
        <span class="eco-eyebrow"><i class="fas fa-chart-line"></i> MACROECONOMIC INTELLIGENCE</span>
        <h1>Global Economy Dashboard</h1>
        <p>Bandingkan skala ekonomi, inflasi, pertumbuhan, dan eksposur perdagangan global dari data World Bank.</p>
    </div>
    <div class="eco-actions">
        <a class="eco-source" href="https://data.worldbank.org" target="_blank" rel="noopener"><i class="fas fa-database"></i><span>DATA SOURCE<small>World Bank Open Data</small></span><i class="fas fa-external-link-alt"></i></a>
        <a href="{{ route('comparison.index') }}" class="eco-btn"><i class="fas fa-balance-scale"></i> Compare Countries</a>
    </div>
</div>
@stop

@section('content')
@php
    $selectedEconomy = $economyData->firstWhere('code', $trendCode);
    $coverage = $economyData->count();
    $selectedExports = data_get($selectedEconomy, 'exports');
    $selectedImports = data_get($selectedEconomy, 'imports');
    $tradeBalance = $selectedExports !== null && $selectedImports !== null
        ? $selectedExports - $selectedImports
        : null;
@endphp
<div class="eco-workspace">
    <section class="eco-kpis">
        @foreach([
            ['tone'=>'blue','icon'=>'fa-landmark','label'=>'Combined GDP','value'=>'$'.number_format($totalGdp/1e12,1).'T','note'=>$coverage.' countries available'],
            ['tone'=>'violet','icon'=>'fa-users','label'=>'Total Population','value'=>number_format($totalPopulation/1e9,2).'B','note'=>'Combined consumer markets'],
            ['tone'=>'amber','icon'=>'fa-percentage','label'=>'Average Inflation','value'=>number_format($averageInflation,2).'%','note'=>'Consumer price movement'],
            ['tone'=>'green','icon'=>'fa-chart-line','label'=>'Average GDP Growth','value'=>number_format($averageGrowth,2).'%','note'=>'Real annual growth'],
            ['tone'=>'cyan','icon'=>'fa-trophy','label'=>'Largest Economy','value'=>$largestEconomy['name'] ?? '—','note'=>isset($largestEconomy['gdp'])?'$'.number_format($largestEconomy['gdp']/1e12,2).' trillion GDP':'Data unavailable'],
        ] as $kpi)
        <article class="eco-kpi {{ $kpi['tone'] }}">
            <span class="eco-kpi-icon"><i class="fas {{ $kpi['icon'] }}"></i></span>
            <div><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] }}</strong><p>{{ $kpi['note'] }}</p></div>
            <i class="fas {{ $kpi['icon'] }} eco-kpi-mark"></i>
        </article>
        @endforeach
    </section>

    <section class="eco-control-panel">
        <div class="eco-selection">
            <span class="eco-selection-flag">
                @if($trendCode)<img src="https://flagcdn.com/w80/{{ strtolower($trendCode) }}.png" alt="{{ $trendCountry['name'] }} flag">@else<i class="fas fa-globe"></i>@endif
            </span>
            <div><span class="eco-eyebrow">SELECTED MARKET</span><h2>{{ $trendCountry['name'] }}</h2><p>Historical intelligence and latest trade indicators</p></div>
        </div>
        <div class="eco-trade-metrics">
            <div><i class="fas fa-file-export"></i><span>Exports<strong>{{ $selectedExports !== null ? '$'.number_format($selectedExports/1e9,1).'B' : 'Not reported' }}</strong></span></div>
            <div><i class="fas fa-file-import"></i><span>Imports<strong>{{ $selectedImports !== null ? '$'.number_format($selectedImports/1e9,1).'B' : 'Not reported' }}</strong></span></div>
        </div>
        <form method="GET" class="eco-country-filter" id="ecoCountryForm">
            <label for="ecoCountrySearch">MARKET SCOPE</label>
            <div class="eco-country-picker" data-country-picker>
                <i class="fas fa-search"></i>
                <input id="ecoCountrySearch" type="search" value="{{ $trendCountry['name'] }} · {{ $trendCode }}" placeholder="Search country or code" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="ecoCountryOptions" @disabled($trendCountries->isEmpty())>
                <button type="button" aria-label="Open country list"><i class="fas fa-chevron-down"></i></button>
                <input type="hidden" name="country" value="{{ $trendCode }}">
                <div class="eco-country-options" id="ecoCountryOptions" role="listbox" hidden>
                    <div class="eco-country-options-head"><span>SELECT A MARKET</span><small>{{ $trendCountries->count() }} available</small></div>
                    <div class="eco-country-options-list">
                    @forelse($trendCountries as $code=>$meta)
                        <button type="button" role="option" data-code="{{ $code }}" data-name="{{ $meta['name'] }}" @if($trendCode===$code) aria-selected="true" class="selected" @endif>
                            <img src="https://flagcdn.com/w40/{{ strtolower($code) }}.png" alt="">
                            <span>{{ $meta['name'] }}<small>{{ $code }}</small></span>
                            <i class="fas fa-check"></i>
                        </button>
                    @empty
                        <div class="eco-picker-empty">No countries available</div>
                    @endforelse
                    </div>
                    <div class="eco-picker-empty eco-filter-empty" hidden>No matching country found.</div>
                </div>
            </div>
        </form>
    </section>

    <div class="eco-chart-grid">
        <section class="eco-panel eco-chart-panel">
            <header class="eco-panel-head">
                <div><span class="eco-eyebrow">GDP TREND</span><h2>{{ $trendCountry['name'] }} Gross Domestic Product</h2><p>Historical economic output at current market prices.</p></div>
                <span class="eco-unit"><i class="fas fa-dollar-sign"></i> Current USD · Trillion</span>
            </header>
            <div class="eco-chart">
                @if(count($gdpTrend)>1)<canvas id="gdpTrendChart"></canvas>
                @else<div class="eco-empty"><i class="fas fa-chart-area"></i><strong>Historical GDP data unavailable.</strong><span>World Bank has no recent observations for this market.</span></div>@endif
            </div>
        </section>
        <section class="eco-panel eco-chart-panel">
            <header class="eco-panel-head">
                <div><span class="eco-eyebrow">INFLATION TREND</span><h2>{{ $trendCountry['name'] }} Consumer Prices</h2><p>Annual movement in the consumer price index.</p></div>
                <span class="eco-unit amber"><i class="fas fa-percentage"></i> Annual Change</span>
            </header>
            <div class="eco-chart">
                @if(count($inflationTrend)>1)<canvas id="inflationTrendChart"></canvas>
                @else<div class="eco-empty"><i class="fas fa-chart-line"></i><strong>Historical inflation data unavailable.</strong><span>World Bank has no recent observations for this market.</span></div>@endif
            </div>
        </section>
    </div>

    <section class="eco-panel eco-trade-panel">
        <header class="eco-panel-head">
            <div><span class="eco-eyebrow">INTERNATIONAL TRADE FLOW</span><h2>{{ $trendCountry['name'] }} Exports vs Imports</h2><p>World Bank goods and services values with calculated trade balance.</p></div>
            <div class="eco-trade-summary">
                <span><i class="exports"></i>Exports <strong>{{ $selectedExports !== null ? '$'.number_format($selectedExports/1e9,1).'B' : 'Not reported' }}</strong></span>
                <span><i class="imports"></i>Imports <strong>{{ $selectedImports !== null ? '$'.number_format($selectedImports/1e9,1).'B' : 'Not reported' }}</strong></span>
                <span class="{{ ($tradeBalance ?? 0) >= 0 ? 'surplus' : 'deficit' }}">Balance <strong>{{ $tradeBalance !== null ? (($tradeBalance >= 0 ? '+' : '-').'$'.number_format(abs($tradeBalance)/1e9,1).'B') : 'Not reported' }}</strong></span>
            </div>
        </header>
        <div class="eco-trade-chart">
            @if(count($exportTrend)>1 || count($importTrend)>1)
                <canvas id="tradeFlowChart"></canvas>
            @else
                <div class="eco-empty"><i class="fas fa-exchange-alt"></i><strong>Historical trade data unavailable.</strong><span>World Bank has no recent export or import observations for this market.</span></div>
            @endif
        </div>
    </section>

    <section class="eco-panel eco-directory">
        <header class="eco-panel-head directory-head">
            <div><span class="eco-eyebrow">GLOBAL MARKET DIRECTORY</span><h2>Economic Coverage</h2><p>{{ $coverage }} economies available in the intelligence dataset.</p></div>
            <div class="eco-legend"><span><i class="low"></i>Stable</span><span><i class="medium"></i>Monitor</span><span><i class="high"></i>Elevated</span></div>
        </header>
        <div class="eco-market-grid">
            @foreach($economyData as $item)
                @php
                    $inflation = abs((float)($item['inflation'] ?? 0));
                    $tone = $inflation > 6 ? 'high' : ($inflation > 3 ? 'medium' : 'low');
                @endphp
                <article class="eco-market-card {{ $tone }} {{ $item['code']===$trendCode?'selected':'' }}">
                    <header>
                        <span class="eco-flag"><b>{{ strtoupper($item['alpha2']) }}</b><img src="https://flagcdn.com/w80/{{ $item['alpha2'] }}.png" alt="{{ $item['name'] }} flag" loading="lazy" onerror="this.remove()"></span>
                        <div><small>{{ $item['region'] }} · {{ $item['code'] }}</small><h3>{{ $item['name'] }}</h3></div>
                        <span class="eco-stability"><i></i>{{ $tone==='low'?'Stable':($tone==='medium'?'Monitor':'Elevated') }}</span>
                    </header>
                    <div class="eco-gdp"><span>GROSS DOMESTIC PRODUCT</span><strong class="{{ $item['gdp'] === null ? 'not-reported' : '' }}">{{ $item['gdp'] !== null ? '$'.number_format($item['gdp']/1e12,2).'T' : 'Not reported' }}</strong><small>{{ $item['gdp_year'] ? 'Latest observation · '.$item['gdp_year'] : 'World Bank coverage unavailable' }}</small></div>
                    <div class="eco-market-metrics">
                        <div><span>Growth</span><strong class="{{ $item['growth']!==null?(($item['growth']??0)>=0?'positive':'negative'):'not-reported' }}">{{ $item['growth']!==null?number_format($item['growth'],2).'%':'Not reported' }}</strong></div>
                        <div><span>Inflation</span><strong class="{{ $item['inflation']===null?'not-reported':'' }}">{{ $item['inflation']!==null?number_format($item['inflation'],2).'%':'Not reported' }}</strong></div>
                        <div><span>Trade/GDP</span><strong class="{{ $item['trade']===null?'not-reported':'' }}">{{ $item['trade']!==null?number_format($item['trade'],1).'%':'Not reported' }}</strong></div>
                    </div>
                    <a href="{{ route('economy.index',['country'=>$item['code']]) }}">Analyze market <i class="fas fa-arrow-right"></i></a>
                </article>
            @endforeach
        </div>
    </section>

    <section class="eco-panel eco-table-panel">
        <header class="eco-panel-head directory-head">
            <div><span class="eco-eyebrow">WORLD BANK INDICATORS</span><h2>Economic Data Table</h2><p>Latest available macroeconomic observations.</p></div>
            <span class="eco-updated"><i class="fas fa-shield-alt"></i> Cached 6 hours</span>
        </header>
        <div class="table-responsive">
            <table class="eco-table">
                <thead><tr><th>#</th><th>Country</th><th>GDP (Current USD)</th><th>Population</th><th>GDP Growth</th><th>Inflation</th><th>Trade Exposure</th><th>Data Year</th></tr></thead>
                <tbody>
                @foreach($economyData as $item)
                    @php $tone=abs((float)($item['inflation']??0))>6?'high':(abs((float)($item['inflation']??0))>3?'medium':'low'); @endphp
                    <tr>
                        <td class="eco-row-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</td>
                        <td><div class="eco-country-cell"><span class="eco-flag"><b>{{ strtoupper($item['alpha2']) }}</b><img src="https://flagcdn.com/w80/{{ $item['alpha2'] }}.png" alt="{{ $item['name'] }} flag" loading="lazy" onerror="this.remove()"></span><div><strong>{{ $item['name'] }}</strong><small>{{ $item['region'] }} · {{ $item['code'] }}</small></div></div></td>
                        <td><strong class="eco-money">{{ $item['gdp']!==null?'$'.number_format($item['gdp']):'—' }}</strong></td>
                        <td>{{ $item['population']!==null?number_format($item['population']):'—' }}</td>
                        <td><span class="eco-growth {{ ($item['growth']??0)>=0?'positive':'negative' }}"><i class="fas fa-caret-{{ ($item['growth']??0)>=0?'up':'down' }}"></i> {{ $item['growth']!==null?number_format(abs($item['growth']),2).'%':'—' }}</span></td>
                        <td><span class="eco-inflation {{ $tone }}">{{ $item['inflation']!==null?number_format($item['inflation'],2).'%':'—' }}</span></td>
                        <td>{{ $item['trade']!==null?number_format($item['trade'],1).'%':'—' }}</td>
                        <td><span class="eco-year">{{ $item['gdp_year']??'—' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <footer class="eco-methodology">
            <i class="fas fa-info-circle"></i>
            <p><strong>Data methodology</strong><span>Values use the latest non-null World Bank observation available. External API responses are cached for six hours.</span></p>
            <a href="https://data.worldbank.org" target="_blank" rel="noopener">Open World Bank Data <i class="fas fa-external-link-alt"></i></a>
        </footer>
    </section>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}">
<style>
.eco-workspace{--eco-bg:#061421;--eco-panel:#0a1c2e;--eco-panel2:#0d2339;--eco-line:#1b3b56;--eco-text:#eaf4fc;--eco-muted:#718aa2;--eco-blue:#2d91ff;padding-bottom:12px}.eco-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:7px 0 10px}.eco-eyebrow{display:block;color:#3495ff;font-size:9px;font-weight:850;letter-spacing:1.35px}.eco-eyebrow i{margin-right:6px}.eco-hero h1{margin:7px 0 4px!important;color:#f1f7fd!important;font-size:28px!important;letter-spacing:-.6px}.eco-hero p{max-width:680px;margin:0;color:#7791aa!important;font-size:12px}.eco-actions{display:flex;align-items:center;gap:9px}.eco-source{display:flex;align-items:center;gap:8px;min-height:42px;padding:0 12px;border:1px solid #214968;border-radius:10px;background:#0a2034;color:#85a8c6;text-decoration:none}.eco-source>i:first-child{color:#4da3ee}.eco-source>i:last-child{color:#496a85;font-size:8px}.eco-source span,.eco-source small{display:block}.eco-source span{font-size:7px;font-weight:850;letter-spacing:.7px}.eco-source small{margin-top:2px;color:#b7cee1;font-size:9px;letter-spacing:0}.eco-source:hover{border-color:#367bb2;color:#a6cce9;text-decoration:none}.eco-btn{display:flex;align-items:center;gap:8px;min-height:42px;padding:0 15px;border:1px solid #3b97ff;border-radius:10px;background:linear-gradient(135deg,#2d91ff,#1466d8);box-shadow:0 9px 24px rgba(26,116,221,.25);color:#fff;font-size:11px;font-weight:800;text-decoration:none}.eco-btn:hover{transform:translateY(-2px);color:#fff;text-decoration:none}
.eco-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:15px}.eco-kpi{--tone:#2d91ff;--soft:rgba(45,145,255,.12);position:relative;display:flex;align-items:center;min-width:0;min-height:104px;padding:16px;overflow:hidden;border:1px solid #1a3a55;border-radius:14px;background:linear-gradient(145deg,#0d2238,#081827);box-shadow:0 12px 30px rgba(0,0,0,.16);transition:.22s}.eco-kpi.violet{--tone:#9c7dff;--soft:rgba(156,125,255,.12)}.eco-kpi.amber{--tone:#ffae35;--soft:rgba(255,174,53,.12)}.eco-kpi.green{--tone:#31ce7d;--soft:rgba(49,206,125,.12)}.eco-kpi.cyan{--tone:#38c6dd;--soft:rgba(56,198,221,.12)}.eco-kpi:hover{transform:translateY(-3px);border-color:color-mix(in srgb,var(--tone) 55%,#1a3a55)}.eco-kpi-icon{display:grid;place-items:center;width:46px;height:46px;flex:0 0 46px;margin-right:11px;border:1px solid color-mix(in srgb,var(--tone) 30%,transparent);border-radius:12px;background:var(--soft);color:var(--tone);font-size:16px}.eco-kpi small,.eco-kpi strong,.eco-kpi p{display:block;margin:0}.eco-kpi small{overflow:hidden;color:#718ca5;font-size:8px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.eco-kpi strong{overflow:hidden;margin:4px 0;color:#f1f7fd;font-size:19px;line-height:1;text-overflow:ellipsis;white-space:nowrap}.eco-kpi p{overflow:hidden;color:#4f6d87;font-size:7px;text-overflow:ellipsis;white-space:nowrap}.eco-kpi-mark{position:absolute;right:-9px;bottom:-13px;color:var(--tone);font-size:58px;opacity:.035;transform:rotate(-10deg)}
.eco-control-panel{display:grid;grid-template-columns:minmax(220px,1fr) auto minmax(230px,300px);align-items:center;gap:18px;margin-bottom:15px;padding:15px 17px;border:1px solid #1d4260;border-radius:14px;background:linear-gradient(100deg,#102b46,#0a1d30 58%,#081827);box-shadow:0 12px 32px rgba(0,0,0,.18)}.eco-selection{display:flex;align-items:center;min-width:0}.eco-selection-flag{display:grid;place-items:center;width:50px;height:38px;flex:0 0 50px;margin-right:11px;overflow:hidden;border:1px solid #315573;border-radius:8px;background:#0d2a43;color:#58a8ee}.eco-selection-flag img{width:100%;height:100%;object-fit:cover}.eco-selection h2{overflow:hidden;margin:2px 0!important;color:#eef6fc!important;font-size:14px!important;text-overflow:ellipsis;white-space:nowrap}.eco-selection p{margin:0;color:#63819a!important;font-size:8px}.eco-trade-metrics{display:flex;gap:8px}.eco-trade-metrics>div{display:flex;align-items:center;gap:8px;min-width:120px;padding:9px 11px;border:1px solid #1a405d;border-radius:9px;background:rgba(5,20,34,.56)}.eco-trade-metrics i{color:#37c982}.eco-trade-metrics>div:last-child i{color:#ff9c3d}.eco-trade-metrics span,.eco-trade-metrics strong{display:block}.eco-trade-metrics span{color:#5f7c95;font-size:7px}.eco-trade-metrics strong{margin-top:2px;color:#dcebf7;font-size:11px}.eco-country-filter label{display:block;margin-bottom:5px;color:#62819c;font-size:7px;font-weight:850;letter-spacing:.8px}.eco-country-filter>div{display:grid;grid-template-columns:22px 1fr 15px;align-items:center;height:40px;padding:0 9px;border:1px solid #285578;border-radius:9px;background:#061827}.eco-country-filter i{color:#5684a8;font-size:9px}.eco-country-filter select{width:100%;height:100%;padding:0!important;border:0!important;background:transparent!important;color:#d9e9f7!important;font-size:10px;font-weight:700;box-shadow:none!important;appearance:none}
.eco-chart-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:13px;margin-bottom:15px}.eco-panel{overflow:hidden;border:1px solid var(--eco-line);border-radius:15px;background:linear-gradient(145deg,rgba(12,31,50,.99),rgba(7,21,35,.99));box-shadow:0 14px 38px rgba(0,0,0,.2)}.eco-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:17px 18px;border-bottom:1px solid #18364f;background:linear-gradient(90deg,rgba(17,45,71,.5),rgba(8,24,40,.12))}.eco-panel-head h2{margin:4px 0 2px!important;color:var(--eco-text)!important;font-size:15px!important}.eco-panel-head p{margin:0;color:#66829b!important;font-size:8px}.eco-unit,.eco-updated{display:flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid #27577e;border-radius:999px;background:#0d3151;color:#72b9ff;font-size:8px;font-weight:800;white-space:nowrap}.eco-unit.amber{border-color:#62491c;background:#2d240f;color:#ffc258}.eco-chart{height:305px;padding:14px 14px 10px}.eco-empty{display:flex;align-items:center;justify-content:center;flex-direction:column;height:100%;border:1px dashed #23435d;border-radius:11px;background:rgba(6,20,33,.45);color:#63819b}.eco-empty i{margin-bottom:9px;color:#3b719e;font-size:24px}.eco-empty strong{color:#9cb4c9;font-size:10px}.eco-empty span{margin-top:3px;font-size:8px}
.eco-directory,.eco-table-panel{margin-bottom:15px}.directory-head{align-items:center}.eco-legend{display:flex;gap:13px;color:#6d879f;font-size:8px}.eco-legend span{display:flex;align-items:center;gap:5px}.eco-legend i{width:7px;height:7px;border-radius:50%}.eco-legend .low{background:#31ce7d}.eco-legend .medium{background:#f5b928}.eco-legend .high{background:#ff5967}.eco-market-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;padding:15px}.eco-market-card{--risk:#31ce7d;position:relative;min-width:0;padding:12px;border:1px solid #193b56;border-radius:12px;background:linear-gradient(145deg,#0d243a,#081827);transition:.2s}.eco-market-card.medium{--risk:#f5b928}.eco-market-card.high{--risk:#ff5967}.eco-market-card:hover,.eco-market-card.selected{transform:translateY(-2px);border-color:#2b6b9e;box-shadow:0 13px 27px rgba(0,0,0,.2)}.eco-market-card.selected:before{content:"SELECTED";position:absolute;top:-7px;right:10px;padding:2px 6px;border-radius:5px;background:#2388e8;color:#fff;font-size:6px;font-weight:900;letter-spacing:.7px}.eco-market-card>header{display:grid;grid-template-columns:42px minmax(0,1fr) auto;align-items:center;gap:9px}.eco-flag{position:relative;display:grid;place-items:center;width:42px;height:30px;overflow:hidden;border:1px solid #31516c;border-radius:7px;background:#10263b;color:#8db7d8;font-size:8px;font-weight:800}.eco-flag img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.eco-market-card header small,.eco-market-card header h3{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.eco-market-card header small{color:#587691;font-size:6px}.eco-market-card header h3{margin:2px 0!important;color:#deebf7!important;font-size:10px!important}.eco-stability{display:flex;align-items:center;gap:4px;color:var(--risk);font-size:6px;font-weight:850}.eco-stability i{width:5px;height:5px;border-radius:50%;background:var(--risk);box-shadow:0 0 0 3px color-mix(in srgb,var(--risk) 12%,transparent)}.eco-gdp{margin:11px 0;padding:10px;border:1px solid #17364f;border-radius:9px;background:rgba(5,19,32,.62)}.eco-gdp span,.eco-gdp strong,.eco-gdp small{display:block}.eco-gdp span{color:#58758e;font-size:6px;letter-spacing:.5px}.eco-gdp strong{margin:3px 0;color:#f0f6fc;font-size:18px}.eco-gdp small{color:#49657e;font-size:6px}.eco-market-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:5px}.eco-market-metrics>div{padding-left:7px;border-left:1px solid #1a3a53}.eco-market-metrics span,.eco-market-metrics strong{display:block}.eco-market-metrics span{color:#56738d;font-size:6px}.eco-market-metrics strong{margin-top:2px;color:#a9bfd2;font-size:8px}.positive{color:#38d487!important}.negative{color:#ff6b76!important}.eco-market-card>a{display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:9px;border-top:1px solid #17344c;color:#5daaf0;font-size:7px;font-weight:800;text-decoration:none}.eco-market-card>a:hover{color:#b4dcff}.eco-market-card>a i{transition:.2s}.eco-market-card>a:hover i{transform:translateX(3px)}
.eco-table{width:100%;min-width:1080px;border-collapse:collapse}.eco-table th{padding:12px 14px;border-bottom:1px solid #1d3c55;background:#0b2136;color:#66859f;font-size:8px;letter-spacing:.65px;text-align:left;text-transform:uppercase}.eco-table td{padding:11px 14px;border-bottom:1px solid #153049;color:#819bb2;font-size:9px;vertical-align:middle}.eco-table tbody tr{transition:.15s}.eco-table tbody tr:hover{background:#0d263e}.eco-row-number{color:#47657e!important;font-family:monospace}.eco-country-cell{display:flex;align-items:center;gap:9px}.eco-country-cell .eco-flag{width:36px;height:25px}.eco-country-cell strong,.eco-country-cell small{display:block}.eco-country-cell strong{color:#dce9f4;font-size:10px}.eco-country-cell small{margin-top:2px;color:#506d86;font-size:7px}.eco-money{color:#bed1e2;font-size:9px}.eco-growth{font-size:8px;font-weight:800}.eco-inflation{display:inline-flex;padding:4px 7px;border-radius:999px;font-size:7px;font-weight:850}.eco-inflation.low{background:#0c3729;color:#4ddb8e}.eco-inflation.medium{background:#3a2b0b;color:#f5c649}.eco-inflation.high{background:#3d1920;color:#ff747e}.eco-year{padding:4px 6px;border:1px solid #24435d;border-radius:6px;background:#0b1d30;color:#89a3b9;font-size:7px}.eco-methodology{display:flex;align-items:center;gap:10px;padding:12px 15px;border-top:1px solid #1b3a54;background:#081928;color:#5982a5}.eco-methodology>i{color:#4295da}.eco-methodology p{margin:0}.eco-methodology strong,.eco-methodology span{display:block}.eco-methodology strong{color:#8db0cb;font-size:8px}.eco-methodology span{color:#54718a;font-size:7px}.eco-methodology a{margin-left:auto;color:#5eaaf0;font-size:8px;font-weight:800}
.eco-country-filter{position:relative;z-index:30}.eco-country-filter>div.eco-country-picker{position:relative;display:grid;grid-template-columns:22px minmax(0,1fr) 30px;align-items:center;height:42px;padding:0 5px 0 10px;overflow:visible;border:1px solid #2c628b;border-radius:10px;background:#061827;box-shadow:inset 0 0 0 1px rgba(67,145,207,.04)}.eco-country-picker:focus-within{border-color:#3c9fff;box-shadow:0 0 0 3px rgba(45,145,255,.12)}.eco-country-picker>input[type=search]{width:100%;height:38px;padding:0 6px!important;border:0!important;background:transparent!important;color:#e3eff9!important;font-size:10px;font-weight:750;box-shadow:none!important}.eco-country-picker>input[type=search]::-webkit-search-cancel-button{filter:invert(1);opacity:.45}.eco-country-picker>button{display:grid;place-items:center;width:29px;height:29px;padding:0;border:0;border-radius:7px;background:#0d2c47;color:#72a8d2;cursor:pointer}.eco-country-picker>button:hover{background:#123c60;color:#b8dcfa}.eco-country-options{position:absolute;z-index:1000;top:calc(100% + 8px);right:-1px;left:-1px;overflow:hidden;border:1px solid #285c83;border-radius:12px;background:#071a2b;box-shadow:0 22px 55px rgba(0,0,0,.55)}.eco-country-options-head{display:flex;align-items:center;justify-content:space-between;padding:10px 11px;border-bottom:1px solid #183b57;background:#0d2942}.eco-country-options-head span{color:#5caaf0;font-size:7px;font-weight:900;letter-spacing:.9px}.eco-country-options-head small{color:#56758f;font-size:7px}.eco-country-options-list{max-height:300px;padding:5px;overflow-y:auto}.eco-country-options-list>button{display:grid;grid-template-columns:31px minmax(0,1fr) 18px;align-items:center;gap:8px;width:100%;min-height:43px;padding:5px 8px;border:0;border-radius:8px;background:transparent;color:#aac0d3;text-align:left;cursor:pointer}.eco-country-options-list>button:hover,.eco-country-options-list>button.active{background:#102e49;color:#fff}.eco-country-options-list>button.selected{background:#0d3b63;color:#dcefff}.eco-country-options-list img{width:31px;height:22px;border:1px solid #31516b;border-radius:5px;object-fit:cover}.eco-country-options-list span,.eco-country-options-list small{display:block}.eco-country-options-list span{overflow:hidden;font-size:9px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.eco-country-options-list small{margin-top:2px;color:#53728d;font-size:7px}.eco-country-options-list button>i{display:none;color:#49b2ff}.eco-country-options-list button.selected>i{display:block}.eco-picker-empty{padding:19px;color:#627f98;font-size:9px;text-align:center}
.eco-trade-panel{margin-bottom:15px}.eco-trade-summary{display:flex;align-items:center;gap:7px;flex-wrap:wrap;justify-content:flex-end}.eco-trade-summary>span{display:flex;align-items:center;gap:5px;padding:6px 8px;border:1px solid #23445e;border-radius:8px;background:#091c2c;color:#6f8aa2;font-size:7px}.eco-trade-summary>span>i{width:7px;height:7px;border-radius:50%}.eco-trade-summary .exports{background:#36d18a;box-shadow:0 0 7px rgba(54,209,138,.4)}.eco-trade-summary .imports{background:#ff9d35;box-shadow:0 0 7px rgba(255,157,53,.4)}.eco-trade-summary strong{color:#c7dae9;font-size:8px}.eco-trade-summary .surplus{border-color:#205c43;color:#46d890}.eco-trade-summary .deficit{border-color:#65333b;color:#ff747f}.eco-trade-chart{height:300px;padding:14px 16px 11px}
.eco-market-card .not-reported{color:#607d96!important;font-size:7px!important;font-weight:700!important}
@media(max-width:1250px){.eco-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.eco-market-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.eco-control-panel{grid-template-columns:1fr auto}.eco-country-filter{grid-column:1/-1}.eco-country-filter>div{max-width:100%}}
@media(max-width:991px){.eco-hero{align-items:flex-start;flex-direction:column}.eco-actions{width:100%}.eco-source,.eco-btn{flex:1}.eco-chart-grid{grid-template-columns:1fr}.eco-market-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.eco-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.eco-control-panel{grid-template-columns:1fr}.eco-trade-metrics{width:100%}.eco-trade-metrics>div{flex:1}.eco-market-grid{grid-template-columns:1fr}.eco-panel-head{align-items:flex-start;flex-direction:column}.eco-trade-summary{justify-content:flex-start}.eco-methodology a{display:none}.eco-actions{flex-direction:column}.eco-source,.eco-btn{width:100%;justify-content:center}}
@media(max-width:450px){.eco-kpis{grid-template-columns:1fr}.eco-trade-metrics{flex-direction:column}.eco-chart{height:260px}}
</style>
@stop

@section('js')
<script>
const gdpTrend=@json($gdpTrend),inflationTrend=@json($inflationTrend),exportTrend=@json($exportTrend),importTrend=@json($importTrend);
function ecoChart(id,points,label,color,format){
    const canvas=document.getElementById(id);if(!canvas||points.length<2||!window.Chart)return;
    const gradient=canvas.getContext('2d').createLinearGradient(0,0,0,300);gradient.addColorStop(0,color+'3b');gradient.addColorStop(1,color+'00');
    new Chart(canvas,{type:'line',data:{labels:points.map(p=>p.year),datasets:[{data:points.map(p=>p.value),borderColor:color,backgroundColor:gradient,borderWidth:2.4,pointRadius:2.5,pointHoverRadius:6,pointBackgroundColor:'#081827',pointBorderColor:color,pointBorderWidth:2,tension:.32,fill:true}]},options:{maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},plugins:{legend:{display:false},tooltip:{displayColors:false,backgroundColor:'#071827',borderColor:'#28516f',borderWidth:1,titleColor:'#7e9bb4',bodyColor:'#e8f2fa',padding:10,callbacks:{label:c=>label+': '+format(c.raw)}}},scales:{x:{grid:{display:false},border:{display:false},ticks:{color:'#58758e',font:{size:8,weight:'600'},maxTicksLimit:10}},y:{position:'right',border:{display:false},grid:{color:'rgba(88,125,154,.16)'},ticks:{color:'#58758e',font:{size:8},callback:format}}}}});
}
ecoChart('gdpTrendChart',gdpTrend,'GDP','#2d91ff',v=>'$'+(v/1e12).toFixed(2)+'T');
ecoChart('inflationTrendChart',inflationTrend,'Inflation','#ff9d35',v=>Number(v).toFixed(2)+'%');

const tradeCanvas=document.getElementById('tradeFlowChart');
if(tradeCanvas&&window.Chart&&(exportTrend.length>1||importTrend.length>1)){
    const years=[...new Set([...exportTrend,...importTrend].map(point=>point.year))].sort();
    const values=(points)=>{const indexed=Object.fromEntries(points.map(point=>[point.year,point.value]));return years.map(year=>indexed[year]??null)};
    new Chart(tradeCanvas,{type:'line',data:{labels:years,datasets:[
        {label:'Exports',data:values(exportTrend),borderColor:'#36d18a',backgroundColor:'rgba(54,209,138,.10)',borderWidth:2.3,pointRadius:2,pointHoverRadius:5,pointBackgroundColor:'#081827',pointBorderColor:'#36d18a',pointBorderWidth:2,tension:.3,fill:false,spanGaps:true},
        {label:'Imports',data:values(importTrend),borderColor:'#ff9d35',backgroundColor:'rgba(255,157,53,.10)',borderWidth:2.3,pointRadius:2,pointHoverRadius:5,pointBackgroundColor:'#081827',pointBorderColor:'#ff9d35',pointBorderWidth:2,tension:.3,fill:false,spanGaps:true}
    ]},options:{maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},plugins:{legend:{position:'top',align:'end',labels:{color:'#7894ad',boxWidth:8,boxHeight:8,usePointStyle:true,pointStyle:'circle',font:{size:8}}},tooltip:{displayColors:true,backgroundColor:'#071827',borderColor:'#28516f',borderWidth:1,titleColor:'#7e9bb4',bodyColor:'#e8f2fa',padding:10,callbacks:{label:c=>c.dataset.label+': $'+(Number(c.raw)/1e9).toFixed(2)+'B'}}},scales:{x:{grid:{display:false},border:{display:false},ticks:{color:'#58758e',font:{size:8},maxTicksLimit:12}},y:{position:'right',border:{display:false},grid:{color:'rgba(88,125,154,.16)'},ticks:{color:'#58758e',font:{size:8},callback:v=>'$'+(v/1e9).toFixed(0)+'B'}}}}});
}

const currentMarketLabel=@json($trendCountry['name'].' · '.$trendCode);
document.querySelectorAll('[data-country-picker]').forEach(picker=>{
    const search=picker.querySelector('input[type="search"]'),hidden=picker.querySelector('input[type="hidden"]'),toggle=picker.querySelector(':scope > button'),menu=picker.querySelector('.eco-country-options'),options=[...picker.querySelectorAll('[role="option"]')],empty=picker.querySelector('.eco-filter-empty'),form=picker.closest('form');
    let active=-1;
    const visible=()=>options.filter(option=>!option.hidden);
    const open=()=>{menu.hidden=false;search.setAttribute('aria-expanded','true');search.select();filter()};
    const close=()=>{menu.hidden=true;search.setAttribute('aria-expanded','false');active=-1;options.forEach(option=>option.classList.remove('active'))};
    const filter=()=>{const query=search.value.toLowerCase().replace(/\s*·\s*[a-z]{2}$/i,'').trim();let count=0;options.forEach(option=>{option.hidden=query!==''&&!(`${option.dataset.name} ${option.dataset.code}`.toLowerCase().includes(query));if(!option.hidden)count++});empty.hidden=count!==0;active=-1};
    const select=option=>{hidden.value=option.dataset.code;search.value=`${option.dataset.name} · ${option.dataset.code}`;close();form.submit()};
    toggle?.addEventListener('click',()=>menu.hidden?open():close());
    search?.addEventListener('focus',open);
    search?.addEventListener('input',()=>{if(menu.hidden)open();filter()});
    search?.addEventListener('keydown',event=>{const items=visible();if(event.key==='ArrowDown'||event.key==='ArrowUp'){event.preventDefault();active=Math.max(0,Math.min(items.length-1,active+(event.key==='ArrowDown'?1:-1)));items.forEach(item=>item.classList.remove('active'));items[active]?.classList.add('active');items[active]?.scrollIntoView({block:'nearest'})}else if(event.key==='Enter'){event.preventDefault();if(active>=0&&items[active])select(items[active]);else if(items.length===1)select(items[0])}else if(event.key==='Escape'){close();search.value=currentMarketLabel;search.blur()}});
    options.forEach(option=>option.addEventListener('click',()=>select(option)));
    document.addEventListener('click',event=>{if(!picker.contains(event.target))close()});
});
</script>
@stop

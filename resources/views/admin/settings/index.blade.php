@extends('layouts.bootstrap5')
@section('title','Platform Status')
@section('content_header')
<div class="sc-page-head"><div><span class="page-kicker">SYSTEM ADMINISTRATION</span><h1>Platform status</h1><p>Runtime configuration and external data-service overview.</p></div><span class="network-live"><i></i><span><small>APPLICATION</small><strong>Operational</strong></span></span></div>
@stop
@section('content')
<div class="row">
@foreach([
['Open-Meteo','Weather intelligence','cloud-sun','No API key required'],
['RainViewer','Live precipitation radar','broadcast-tower','Public educational radar'],
['World Bank','Economic indicators','chart-line','Public data service'],
['ExchangeRate API','Currency intelligence','money-bill-wave','Cached hourly'],
['News API','News and sentiment','newspaper',config('services.newsapi.key')?'API key configured':'Fallback cache enabled']
] as [$name,$purpose,$icon,$status])
<div class="col-md-6 col-xl"><section class="sc-card system-status"><span><i class="fas fa-{{ $icon }}"></i></span><div><small>DATA SERVICE</small><h2>{{ $name }}</h2><p>{{ $purpose }}</p><em><i></i>{{ $status }}</em></div></section></div>
@endforeach
</div>
<section class="sc-card form-panel mt-2"><div class="form-panel-head"><span><i class="fas fa-shield-alt"></i></span><div><small>PRODUCTION READINESS</small><h2>Configuration safeguards</h2><p>Sensitive values remain managed through the environment file and are never exposed in this interface.</p></div></div><div class="row"><div class="col-md-4"><div class="status-check"><i class="fas fa-check"></i><span><strong>Debug mode</strong><small>{{ config('app.debug') ? 'Enabled — disable before deployment' : 'Disabled for safer operation' }}</small></span></div></div><div class="col-md-4"><div class="status-check"><i class="fas fa-check"></i><span><strong>Application timezone</strong><small>{{ config('app.timezone') }}</small></span></div></div><div class="col-md-4"><div class="status-check"><i class="fas fa-check"></i><span><strong>Environment</strong><small>{{ app()->environment() }}</small></span></div></div></div></section>
@stop
@section('css')<link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}"><style>.system-status{display:flex;gap:12px;min-height:145px;padding:17px;margin-bottom:12px}.system-status>span{display:grid;place-items:center;width:42px;height:42px;flex:0 0 42px;border-radius:11px;background:#102f50;color:#59a8ff}.system-status small,.system-status h2,.system-status p{display:block}.system-status small{color:#438ed8;font-size:7px;font-weight:800;letter-spacing:1px}.system-status h2{margin:2px 0;font-size:14px!important}.system-status p{margin:0;color:#718aa2;font-size:10px}.system-status em{display:block;margin-top:12px;color:#56d991;font-size:8px;font-style:normal}.system-status em i{display:inline-block;width:6px;height:6px;margin-right:5px;border-radius:50%;background:#22c66b}.status-check{display:flex;gap:10px;padding:13px;border:1px solid #193149;border-radius:9px;background:#081725}.status-check>i{color:#45d485}.status-check strong,.status-check small{display:block}.status-check strong{color:#dce8f4;font-size:11px}.status-check small{color:#6d849b;font-size:9px}</style>@stop

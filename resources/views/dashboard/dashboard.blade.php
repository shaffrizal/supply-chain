@extends('adminlte::page')

@section('title','Dashboard')

@section('content_header')
<h1>Supply Chain Dashboard</h1>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalCountry }}</h3>
                <p>Total Countries</p>
            </div>
            <div class="icon">
                <i class="fas fa-globe"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $asia }}</h3>
                <p>Asia</p>
            </div>
            <div class="icon">
                <i class="fas fa-map"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $europe }}</h3>
                <p>Europe</p>
            </div>
            <div class="icon">
                <i class="fas fa-globe-europe"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $america }}</h3>
                <p>America</p>
            </div>
            <div class="icon">
                <i class="fas fa-flag"></i>
            </div>
        </div>
    </div>

</div>

<div class="row">

<div class="col-md-8">

<div class="card">

<div class="card-header">
<h3 class="card-title">Jumlah Negara Berdasarkan Region</h3>
</div>

<div class="card-body">
<canvas id="regionChart"></canvas>
</div>

</div>

</div>

<div class="col-md-4">

<div class="card card-primary">

<div class="card-header">
<h3 class="card-title">Cuaca Jakarta</h3>
</div>

<div class="card-body">

<p><b>Suhu</b> :
{{ $weather['current']['temperature_2m'] ?? '-' }} °C</p>

<p><b>Kelembaban</b> :
{{ $weather['current']['relative_humidity_2m'] ?? '-' }} %</p>

<p><b>Angin</b> :
{{ $weather['current']['wind_speed_10m'] ?? '-' }} km/h</p>

<hr>

<h5>Kurs USD</h5>

<p>IDR :
{{ number_format($exchange['rates']['IDR'] ?? 0,2) }}</p>

<p>EUR :
{{ number_format($exchange['rates']['EUR'] ?? 0,2) }}</p>

<p>JPY :
{{ number_format($exchange['rates']['JPY'] ?? 0,2) }}</p>

</div>

</div>

</div>

</div>

<div class="card">

<div class="card-header">
<h3 class="card-title">5 Negara Terbaru</h3>
</div>

<div class="card-body">

<table class="table table-bordered">

<thead>

<tr>

<th>No</th>
<th>Country</th>
<th>Code</th>
<th>Region</th>
<th>Capital</th>

</tr>

</thead>

<tbody>

@foreach($countries as $country)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $country->country_name }}</td>

<td>{{ $country->country_code }}</td>

<td>{{ $country->region }}</td>

<td>{{ $country->capital }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

@stop

@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById('regionChart'),{

type:'bar',

data:{

labels:['Asia','Europe','Africa','America','Oceania'],

datasets:[{

label:'Countries',

data:[
{{ $asia }},
{{ $europe }},
{{ $africa }},
{{ $america }},
{{ $oceania }}
],

backgroundColor:[
'#0d6efd',
'#198754',
'#ffc107',
'#dc3545',
'#6f42c1'
]

}]

}

});

</script>

@stop
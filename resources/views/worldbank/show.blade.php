<!DOCTYPE html>
<html>
<head>
    <title>World Bank Data</title>
</head>
<body>

<h1>Data World Bank</h1>

@if($gdp)

<h2>{{ $gdp['country'] }}</h2>

<p>
    <b>Tahun GDP :</b>
    {{ $gdp['year'] }}
</p>

<p>
    <b>GDP :</b>
    {{ number_format($gdp['value'],0,',','.') }} USD
</p>

@endif

<hr>

@if($population)

<p>
    <b>Tahun Populasi :</b>
    {{ $population['year'] }}
</p>

<p>
    <b>Jumlah Penduduk :</b>
    {{ number_format($population['value'],0,',','.') }} Jiwa
</p>

@endif

<hr>

@if($inflation)

<p>
    <b>Tahun Inflasi :</b>
    {{ $inflation['year'] }}
</p>

<p>
    <b>Tingkat Inflasi :</b>
    {{ $inflation['value'] }} %
</p>

@endif

</body>
</html>
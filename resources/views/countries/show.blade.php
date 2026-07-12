<!DOCTYPE html>
<html>
<head>
    <title>Detail Negara</title>
</head>
<body>

<h1>Detail Negara</h1>

<hr>

<p><strong>Nama Negara :</strong> {{ $country->country_name }}</p>

<p><strong>Kode Negara :</strong> {{ $country->country_code }}</p>

<p><strong>Region :</strong> {{ $country->region }}</p>

<p><strong>Mata Uang :</strong> {{ $country->currency }}</p>

<p><strong>Ibukota :</strong> {{ $country->capital }}</p>

<hr>

<h2>🌍 Data World Bank</h2>

@if($gdp)
    <p><strong>GDP :</strong> {{ number_format($gdp,0,',','.') }} USD</p>
@else
    <p>GDP tidak tersedia.</p>
@endif

</body>
</html>
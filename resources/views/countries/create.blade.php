<!DOCTYPE html>
<html>
<head>

<title>Tambah Negara</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h2>Tambah Negara</h2>

@if($errors->any())

<div class="alert alert-danger">

<ul>

@foreach($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif

<form action="{{ route('countries.store') }}" method="POST">

@csrf

<div class="mb-3">

<label>Nama Negara</label>

<input type="text"
name="country_name"
class="form-control"
value="{{ old('country_name') }}">

</div>

<div class="mb-3">

<label>Kode Negara</label>

<input type="text"
name="country_code"
class="form-control"
value="{{ old('country_code') }}">

</div>

<div class="mb-3">

<label>Region</label>

<input type="text"
name="region"
class="form-control"
value="{{ old('region') }}">

</div>

<button class="btn btn-success">

Simpan

</button>

<a href="{{ route('countries.index') }}"
class="btn btn-secondary">

Kembali

</a>

</form>

</div>

</body>
</html>
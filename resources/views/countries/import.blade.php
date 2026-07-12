@extends('adminlte::page')

@section('title','Import Countries')

@section('content_header')

<h1>Import Data Negara</h1>

@stop

@section('content')

<div class="card">

<div class="card-body">

@if($errors->any())

<div class="alert alert-danger">

<ul>

@foreach($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif

<form
action="{{ route('countries.import.store') }}"
method="POST"
enctype="multipart/form-data">

@csrf

<div class="mb-3">

<label>Pilih File Excel</label>

<input
type="file"
name="file"
class="form-control"
required>

</div>

<button
class="btn btn-success">

Import

</button>

<a
href="{{ route('countries.index') }}"
class="btn btn-secondary">

Kembali

</a>

</form>

</div>

</div>

@stop
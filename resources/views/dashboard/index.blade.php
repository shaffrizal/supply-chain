@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')

<h1>Country Risk Analysis Dashboard</h1>

@stop

@section('content')

<div class="row">

<div class="col-lg-3">

<div class="small-box bg-primary">

<div class="inner">

<h3>{{ $totalCountries }}</h3>

<p>Total Countries</p>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3>{{ $asia }}</h3>

<p>Asia</p>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3>{{ $europe }}</h3>

<p>Europe</p>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-danger">

<div class="inner">

<h3>{{ $africa }}</h3>

<p>Africa</p>

</div>

</div>

</div>

</div>

@stop
@extends('layouts.bootstrap5')

@section('title', 'Add Country')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="mb-1">Add Country</h1><small class="text-muted">Create a verified country record for the intelligence dataset.</small></div>
    <a href="{{ route('countries.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Public Directory</a>
</div>
@stop

@section('content')
@include('partials.form-errors')
<form action="{{ route('admin.countries.store') }}" method="POST">
    @csrf
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-globe mr-2"></i>Country Dataset Record</h3></div>
        <div class="card-body"><div class="row">
            <div class="col-md-8 form-group"><label>Country name</label><input name="country_name" class="form-control" value="{{ old('country_name') }}" required maxlength="150"></div>
            <div class="col-md-4 form-group"><label>ISO code</label><input name="country_code" class="form-control text-uppercase" value="{{ old('country_code') }}" required maxlength="5"></div>
            <div class="col-md-4 form-group"><label>Region</label><input name="region" class="form-control" value="{{ old('region') }}" required maxlength="100"></div>
            <div class="col-md-4 form-group"><label>Capital</label><input name="capital" class="form-control" value="{{ old('capital') }}" maxlength="100"></div>
            <div class="col-md-4 form-group"><label>Currency</label><input name="currency" class="form-control text-uppercase" value="{{ old('currency') }}" maxlength="20"></div>
            <div class="col-md-4 form-group"><label>Population</label><input type="number" min="0" name="population" class="form-control" value="{{ old('population') }}"></div>
            <div class="col-md-3 form-group"><label>Latitude</label><input type="number" step="0.0000001" min="-90" max="90" name="latitude" class="form-control" value="{{ old('latitude') }}"></div>
            <div class="col-md-3 form-group"><label>Longitude</label><input type="number" step="0.0000001" min="-180" max="180" name="longitude" class="form-control" value="{{ old('longitude') }}"></div>
            <div class="col-md-2 form-group"><label>Risk score</label><input type="number" min="0" max="100" name="risk_index" class="form-control" value="{{ old('risk_index', 30) }}"></div>
        </div></div>
        <div class="card-footer text-right"><a href="{{ route('countries.index') }}" class="btn btn-default mr-2">Cancel</a><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Country</button></div>
    </div>
</form>
@stop

@extends('adminlte::page')

@section('title','Edit Country')

@section('content_header')
    <h1>Edit Country</h1>
@stop

@section('content')

<div class="card card-warning">

    <div class="card-header">
        <h3 class="card-title">Form Edit Country</h3>
    </div>

    <form action="{{ route('countries.update',$country->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nama Negara</label>

                <input
                    type="text"
                    name="country_name"
                    class="form-control"
                    value="{{ old('country_name',$country->country_name) }}"
                    required>
            </div>

            <div class="form-group">
                <label>Kode Negara</label>

                <input
                    type="text"
                    name="country_code"
                    class="form-control"
                    value="{{ old('country_code',$country->country_code) }}"
                    required>
            </div>

            <div class="form-group">
                <label>Region</label>

                <input
                    type="text"
                    name="region"
                    class="form-control"
                    value="{{ old('region',$country->region) }}"
                    required>
            </div>

            <div class="form-group">
                <label>Ibukota</label>

                <input
                    type="text"
                    name="capital"
                    class="form-control"
                    value="{{ old('capital',$country->capital) }}">
            </div>

            <div class="form-group">
                <label>Mata Uang</label>

                <input
                    type="text"
                    name="currency"
                    class="form-control"
                    value="{{ old('currency',$country->currency) }}">
            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i>
                Simpan
            </button>

            <a href="{{ route('countries.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left"></i>
                Kembali

            </a>

        </div>

    </form>

</div>

@stop
@extends('adminlte::page')

@section('title','Countries')

@section('content_header')
<h1>Countries</h1>
@stop

@section('content')

@if(session('success'))

<div class="alert alert-success">

{{ session('success') }}

</div>

@endif

<a href="{{ route('countries.create') }}" class="btn btn-primary mb-3">
Tambah Negara
</a>

<a href="{{ route('countries.import') }}" class="btn btn-success mb-3">
Import Excel
</a>

<div class="card">

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>No</th>
<th>Country</th>
<th>Code</th>
<th>Region</th>
<th>Capital</th>
<th>Aksi</th>

</tr>

</thead>

<tbody>

@forelse($countries as $country)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $country->country_name }}</td>

<td>{{ $country->country_code }}</td>

<td>{{ $country->region }}</td>

<td>{{ $country->capital }}</td>

<td>

    <a href="{{ route('countries.show',$country->id) }}"
       class="btn btn-info btn-sm">

        Detail

    </a>

    <a href="{{ route('countries.edit',$country->id) }}"
       class="btn btn-warning btn-sm">

        Edit

    </a>

    <form
        action="{{ route('countries.destroy',$country->id) }}"
        method="POST"
        style="display:inline;">

        @csrf
        @method('DELETE')

        <button
            class="btn btn-danger btn-sm"
            onclick="return confirm('Yakin ingin menghapus?')">

            Hapus

        </button>

    </form>

</td>

@empty

<tr>

<td colspan="6" class="text-center">

Belum ada data.

</td>

</tr>

@endforelse

</tbody>

</table>

{{ $countries->links() }}

</div>

</div>

@stop
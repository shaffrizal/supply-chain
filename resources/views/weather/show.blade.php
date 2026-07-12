@extends('adminlte::page')

@section('title', 'Weather')

@section('content_header')
<h1>Informasi Cuaca</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <h3>{{ $weather['name'] }}</h3>

        <table class="table table-bordered">

            <tr>
                <th>Negara</th>
                <td>{{ $weather['sys']['country'] }}</td>
            </tr>

            <tr>
                <th>Suhu</th>
                <td>{{ $weather['main']['temp'] }} °C</td>
            </tr>

            <tr>
                <th>Kondisi</th>
                <td>{{ $weather['weather'][0]['description'] }}</td>
            </tr>

            <tr>
                <th>Kelembaban</th>
                <td>{{ $weather['main']['humidity'] }} %</td>
            </tr>

            <tr>
                <th>Kecepatan Angin</th>
                <td>{{ $weather['wind']['speed'] }} m/s</td>
            </tr>

        </table>

    </div>
</div>

@stop
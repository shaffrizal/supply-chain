@extends('adminlte::page')

@section('title', 'Exchange Rate')

@section('content_header')
<h1>Nilai Tukar Mata Uang</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <h3>Base Currency : {{ $data['base_code'] }}</h3>

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Mata Uang</th>
                    <th>Nilai Tukar</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td>IDR</td>
                    <td>{{ number_format($data['conversion_rates']['IDR'],2) }}</td>
                </tr>

                <tr>
                    <td>EUR</td>
                    <td>{{ number_format($data['conversion_rates']['EUR'],2) }}</td>
                </tr>

                <tr>
                    <td>JPY</td>
                    <td>{{ number_format($data['conversion_rates']['JPY'],2) }}</td>
                </tr>

                <tr>
                    <td>MYR</td>
                    <td>{{ number_format($data['conversion_rates']['MYR'],2) }}</td>
                </tr>

                <tr>
                    <td>SGD</td>
                    <td>{{ number_format($data['conversion_rates']['SGD'],2) }}</td>
                </tr>

            </tbody>

        </table>

    </div>
</div>

@stop
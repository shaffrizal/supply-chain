@extends('adminlte::page')

@section('title', 'Risk Score')

@section('content_header')
    <h1>Supply Chain Risk Score</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header bg-primary">

        <h3 class="card-title">
            Analisis Risiko Supply Chain
        </h3>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped table-hover">

            <thead class="text-center">

                <tr>

                    <th>No</th>

                    <th>Country</th>

                    <th>GDP (USD)</th>

                    <th>Population</th>

                    <th>Inflation (%)</th>

                    <th>USD Rate</th>

                    <th>Weather</th>

                    <th>Risk Score</th>

                    <th>Status</th>

                </tr>

            </thead>

            <tbody>

            @foreach($results as $row)

                <tr>

                    <td class="text-center">

                        {{ $loop->iteration }}

                    </td>

                    <td>

                        {{ $row['country']->country_name }}

                    </td>

                    <td class="text-right">

                        {{ $row['gdp'] ? number_format($row['gdp']) : '-' }}

                    </td>

                    <td class="text-right">

                        {{ $row['population'] ? number_format($row['population']) : '-' }}

                    </td>

                    <td class="text-center">

                        {{ $row['inflation'] ?? '-' }}

                    </td>

                    <td class="text-center">

                        {{ $row['exchange_rate'] ?? '-' }}

                    </td>

                    <td class="text-center">

                        @if($row['weather'] == 'Clear')

                            ☀️ Clear

                        @elseif($row['weather'] == 'Clouds')

                            ☁️ Clouds

                        @elseif($row['weather'] == 'Rain')

                            🌧 Rain

                        @elseif($row['weather'] == 'Drizzle')

                            🌦 Drizzle

                        @elseif($row['weather'] == 'Thunderstorm')

                            ⛈ Thunderstorm

                        @elseif($row['weather'] == 'Snow')

                            ❄️ Snow

                        @else

                            {{ $row['weather'] ?? '-' }}

                        @endif

                    </td>

                    <td class="text-center">

                        <strong>

                            {{ $row['risk_score'] }}

                        </strong>

                    </td>

                    <td class="text-center">

                        @if($row['risk'] == 'Low')

                            <span class="badge badge-success">

                                Low

                            </span>

                        @elseif($row['risk'] == 'Medium')

                            <span class="badge badge-warning">

                                Medium

                            </span>

                        @else

                            <span class="badge badge-danger">

                                High

                            </span>

                        @endif

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

@stop
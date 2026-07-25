@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- =============================== --}}
    {{-- PAGE HEADER --}}
    {{-- =============================== --}}

    <div class="row mb-4">

        <div class="col-md-12">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h2 class="fw-bold text-primary mb-1">
                        🌍 Country Comparison Dashboard
                    </h2>

                    <p class="text-muted mb-0">
                        Compare two countries based on Economy, Population,
                        Exchange Rate, Risk Index and Supply Chain Readiness.
                    </p>

                </div>

            </div>

        </div>

    </div>

    {{-- =============================== --}}
    {{-- SELECT COUNTRY --}}
    {{-- =============================== --}}

    <div class="card shadow mb-4">

        <div class="card-header bg-primary text-white">

            <i class="fa fa-globe"></i>

            Select Countries

        </div>

        <div class="card-body">

            <form action="{{ route('comparison.compare') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-5">

                        <label class="form-label fw-bold">

                            Country A

                        </label>

                        <select
                            class="form-select"
                            name="country1"
                            required>

                            <option value="">
                                -- Select Country --
                            </option>

                            @foreach($countries as $country)

                                <option
                                    value="{{ $country->id }}"
                                    {{ optional($country1)->id == $country->id ? 'selected' : '' }}>

                                    {{ $country->country_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-5">

                        <label class="form-label fw-bold">

                            Country B

                        </label>

                        <select
                            class="form-select"
                            name="country2"
                            required>

                            <option value="">
                                -- Select Country --
                            </option>

                            @foreach($countries as $country)

                                <option
                                    value="{{ $country->id }}"
                                    {{ optional($country2)->id == $country->id ? 'selected' : '' }}>

                                    {{ $country->country_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-2 d-grid">

                        <label>&nbsp;</label>

                        <button
                            class="btn btn-primary">

                            <i class="fa fa-chart-column"></i>

                            Compare

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

@if($country1 && $country2)

{{-- =============================== --}}
{{-- COUNTRY INFORMATION --}}
{{-- =============================== --}}

<div class="row">

    {{-- COUNTRY A --}}

    <div class="col-lg-6 mb-4">

        <div class="card shadow h-100">

            <div class="card-header bg-success text-white">

                🌎 {{ $country1->country_name }}

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th width="40%">

                            Capital

                        </th>

                        <td>

                            {{ $country1->capital }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Region

                        </th>

                        <td>

                            {{ $country1->region }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Population

                        </th>

                        <td>

                            {{ number_format($country1->population) }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            GDP

                        </th>

                        <td>

                            ${{ number_format($country1->gdp) }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Exchange Rate

                        </th>

                        <td>

                            {{ $country1->exchange_rate }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Risk Index

                        </th>

                        <td>

                            <span class="badge bg-danger">

                                {{ $country1->risk_index }}

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Risk Level

                        </th>

                        <td>

                            <span class="badge
                            @if($country1->risk_level=='High')
                                bg-danger
                            @elseif($country1->risk_level=='Medium')
                                bg-warning text-dark
                            @else
                                bg-success
                            @endif">

                                {{ $country1->risk_level }}

                            </span>

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

    {{-- COUNTRY B --}}

    <div class="col-lg-6 mb-4">

        <div class="card shadow h-100">

            <div class="card-header bg-info text-white">

                🌎 {{ $country2->country_name }}

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th width="40%">

                            Capital

                        </th>

                        <td>

                            {{ $country2->capital }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Region

                        </th>

                        <td>

                            {{ $country2->region }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Population

                        </th>

                        <td>

                            {{ number_format($country2->population) }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            GDP

                        </th>

                        <td>

                            ${{ number_format($country2->gdp) }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Exchange Rate

                        </th>

                        <td>

                            {{ $country2->exchange_rate }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Risk Index

                        </th>

                        <td>

                            <span class="badge bg-danger">

                                {{ $country2->risk_index }}

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Risk Level

                        </th>

                        <td>

                            <span class="badge
                            @if($country2->risk_level=='High')
                                bg-danger
                            @elseif($country2->risk_level=='Medium')
                                bg-warning text-dark
                            @else
                                bg-success
                            @endif">

                                {{ $country2->risk_level }}

                            </span>

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- ====================================== --}}
{{-- WINNER RECOMMENDATION --}}
{{-- ====================================== --}}

@php

    $winner = $country1->risk_index <= $country2->risk_index
        ? $country1
        : $country2;

@endphp

<div class="card shadow border-success mb-4">

    <div class="card-header bg-success text-white">

        🏆 Recommended Country

    </div>

    <div class="card-body text-center">

        <h2 class="text-success fw-bold">

            {{ $winner->country_name }}

        </h2>

        <p class="text-muted">

            Recommended because it has the lowest Risk Index.

        </p>

        <span class="badge bg-success fs-5 px-4 py-2">

            Risk Index :
            {{ $winner->risk_index }}

        </span>

    </div>

</div>

{{-- ====================================== --}}
{{-- COMPARISON TABLE --}}
{{-- ====================================== --}}

<div class="card shadow mb-4">

    <div class="card-header bg-dark text-white">

        📊 Country Comparison

    </div>

    <div class="card-body">

        <table class="table table-hover table-bordered align-middle">

            <thead class="table-primary">

            <tr>

                <th width="35%">

                    Indicator

                </th>

                <th class="text-center">

                    {{ $country1->country_name }}

                </th>

                <th class="text-center">

                    {{ $country2->country_name }}

                </th>

            </tr>

            </thead>

            <tbody>

                <tr>

                    <th>Capital</th>

                    <td>{{ $country1->capital }}</td>

                    <td>{{ $country2->capital }}</td>

                </tr>

                <tr>

                    <th>Region</th>

                    <td>{{ $country1->region }}</td>

                    <td>{{ $country2->region }}</td>

                </tr>

                <tr>

                    <th>Population</th>

                    <td>{{ number_format($country1->population) }}</td>

                    <td>{{ number_format($country2->population) }}</td>

                </tr>

                <tr>

                    <th>GDP</th>

                    <td>

                        ${{ number_format($country1->gdp) }}

                    </td>

                    <td>

                        ${{ number_format($country2->gdp) }}

                    </td>

                </tr>

                <tr>

                    <th>Exchange Rate</th>

                    <td>{{ $country1->exchange_rate }}</td>

                    <td>{{ $country2->exchange_rate }}</td>

                </tr>

                <tr>

                    <th>Risk Index</th>

                    <td>

                        @if($country1->risk_index < 40)

                            <span class="badge bg-success">

                        @elseif($country1->risk_index < 70)

                            <span class="badge bg-warning text-dark">

                        @else

                            <span class="badge bg-danger">

                        @endif

                            {{ $country1->risk_index }}

                        </span>

                    </td>

                    <td>

                        @if($country2->risk_index < 40)

                            <span class="badge bg-success">

                        @elseif($country2->risk_index < 70)

                            <span class="badge bg-warning text-dark">

                        @else

                            <span class="badge bg-danger">

                        @endif

                            {{ $country2->risk_index }}

                        </span>

                    </td>

                </tr>

                <tr>

                    <th>Risk Level</th>

                    <td>

                        <span class="badge
                        @if($country1->risk_level=='High')
                            bg-danger
                        @elseif($country1->risk_level=='Medium')
                            bg-warning text-dark
                        @else
                            bg-success
                        @endif">

                            {{ $country1->risk_level }}

                        </span>

                    </td>

                    <td>

                        <span class="badge
                        @if($country2->risk_level=='High')
                            bg-danger
                        @elseif($country2->risk_level=='Medium')
                            bg-warning text-dark
                        @else
                            bg-success
                        @endif">

                            {{ $country2->risk_level }}

                        </span>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

{{-- ====================================== --}}
{{-- RISK PROGRESS --}}
{{-- ====================================== --}}

<div class="row mb-4">

    <div class="col-md-6">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                📈 {{ $country1->country_name }}

            </div>

            <div class="card-body">

                <div class="progress" style="height:28px;">

                    <div
                        class="progress-bar
                        @if($country1->risk_index <40)
                            bg-success
                        @elseif($country1->risk_index <70)
                            bg-warning
                        @else
                            bg-danger
                        @endif"

                        style="width:{{ min($country1->risk_index,100) }}%;">

                        {{ $country1->risk_index }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <div class="card shadow">

            <div class="card-header bg-info text-white">

                📈 {{ $country2->country_name }}

            </div>

            <div class="card-body">

                <div class="progress" style="height:28px;">

                    <div
                        class="progress-bar
                        @if($country2->risk_index <40)
                            bg-success
                        @elseif($country2->risk_index <70)
                            bg-warning
                        @else
                            bg-danger
                        @endif"

                        style="width:{{ min($country2->risk_index,100) }}%;">

                        {{ $country2->risk_index }}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- ====================================== --}}
{{-- ANALYSIS SUMMARY --}}
{{-- ====================================== --}}

<div class="card shadow mb-4">

    <div class="card-header bg-secondary text-white">

        📌 Analysis Summary

    </div>

    <div class="card-body">

        @if($country1->risk_index < $country2->risk_index)

            <div class="alert alert-success">

                <h5>

                    ✅ Recommendation

                </h5>

                <p class="mb-0">

                    <strong>{{ $country1->country_name }}</strong>
                    has a lower Risk Index than
                    <strong>{{ $country2->country_name }}</strong>.

                    This country is more suitable for Supply Chain investment.

                </p>

            </div>

        @elseif($country2->risk_index < $country1->risk_index)

            <div class="alert alert-success">

                <h5>

                    ✅ Recommendation

                </h5>

                <p class="mb-0">

                    <strong>{{ $country2->country_name }}</strong>
                    has a lower Risk Index than
                    <strong>{{ $country1->country_name }}</strong>.

                    This country is more suitable for Supply Chain investment.

                </p>

            </div>

        @else

            <div class="alert alert-warning">

                <h5>

                    ⚖ Balanced

                </h5>

                Both countries have the same Risk Index.

            </div>

        @endif

    </div>

</div>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white"><i class="fas fa-satellite-dish mr-2"></i>Live Economic, Weather & Currency Comparison</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead><tr><th>Indicator</th><th>{{ $country1->country_name }}</th><th>{{ $country2->country_name }}</th></tr></thead>
            <tbody>
                <tr><th>Inflation</th><td>{{ data_get($comparisonInsights,$country1->id.'.inflation') !== null ? number_format(data_get($comparisonInsights,$country1->id.'.inflation'),2).'%' : 'Unavailable' }}</td><td>{{ data_get($comparisonInsights,$country2->id.'.inflation') !== null ? number_format(data_get($comparisonInsights,$country2->id.'.inflation'),2).'%' : 'Unavailable' }}</td></tr>
                <tr><th>Weather</th><td>{{ data_get($comparisonInsights,$country1->id.'.temperature') !== null ? number_format(data_get($comparisonInsights,$country1->id.'.temperature'),1).'°C' : 'Unavailable' }} · {{ data_get($comparisonInsights,$country1->id.'.weather_risk','Unknown') }}</td><td>{{ data_get($comparisonInsights,$country2->id.'.temperature') !== null ? number_format(data_get($comparisonInsights,$country2->id.'.temperature'),1).'°C' : 'Unavailable' }} · {{ data_get($comparisonInsights,$country2->id.'.weather_risk','Unknown') }}</td></tr>
                <tr><th>Currency per USD</th><td>{{ data_get($comparisonInsights,$country1->id.'.currency','—') }} {{ data_get($comparisonInsights,$country1->id.'.exchange_rate') !== null ? number_format(data_get($comparisonInsights,$country1->id.'.exchange_rate'),4) : 'Unavailable' }}</td><td>{{ data_get($comparisonInsights,$country2->id.'.currency','—') }} {{ data_get($comparisonInsights,$country2->id.'.exchange_rate') !== null ? number_format(data_get($comparisonInsights,$country2->id.'.exchange_rate'),4) : 'Unavailable' }}</td></tr>
                <tr><th>Exports</th><td>{{ data_get($comparisonInsights,$country1->id.'.exports') !== null ? '$'.number_format(data_get($comparisonInsights,$country1->id.'.exports')/1e9,2).'B' : 'Unavailable' }}</td><td>{{ data_get($comparisonInsights,$country2->id.'.exports') !== null ? '$'.number_format(data_get($comparisonInsights,$country2->id.'.exports')/1e9,2).'B' : 'Unavailable' }}</td></tr>
                <tr><th>Imports</th><td>{{ data_get($comparisonInsights,$country1->id.'.imports') !== null ? '$'.number_format(data_get($comparisonInsights,$country1->id.'.imports')/1e9,2).'B' : 'Unavailable' }}</td><td>{{ data_get($comparisonInsights,$country2->id.'.imports') !== null ? '$'.number_format(data_get($comparisonInsights,$country2->id.'.imports')/1e9,2).'B' : 'Unavailable' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ====================================== --}}
{{-- BAR CHART --}}
{{-- ====================================== --}}

<div class="card shadow">

    <div class="card-header bg-dark text-white">

        📊 Risk Index Comparison

    </div>

    <div class="card-body">

        <canvas id="riskChart" height="120"></canvas>

    </div>

</div>

@endif

@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const chart=document.getElementById('riskChart');

if(chart){

new Chart(chart,{

type:'bar',

data:{

labels:[
'{{ $country1->country_name ?? "" }}',
'{{ $country2->country_name ?? "" }}'
],

datasets:[{

label:'Risk Index',

data:[
{{ $country1->risk_index ?? 0 }},
{{ $country2->risk_index ?? 0 }}
],

backgroundColor:[
'#198754',
'#0d6efd'
],

borderRadius:8,

borderWidth:1

}]

},

options:{

responsive:true,

plugins:{

legend:{

display:false

},

title:{

display:true,

text:'Country Risk Index'

}

},

scales:{

y:{

beginAtZero:true,

max:100

}

}

}

});

}

</script>

@endpush

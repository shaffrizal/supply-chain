@extends('layouts.app')
@section('page_title','Edit Port Facility')
@section('content_header')
<div class="sc-page-head"><div><span class="page-kicker">PORT DATASET</span><h1>Edit port facility</h1><p>Update {{ $port->port_name }} operational information.</p></div></div>
@stop
@section('content')
@include('partials.form-errors')
<form method="POST" action="{{ route('admin.ports.update',$port) }}">@include('ports._form')</form>
@stop

@extends('layouts.app')
@section('page_title','Add Port Facility')
@section('content_header')
<div class="sc-page-head"><div><span class="page-kicker">PORT DATASET</span><h1>Add port facility</h1><p>Register a verified logistics facility in the global network.</p></div></div>
@stop
@section('content')
@include('partials.form-errors')
<form method="POST" action="{{ route('admin.ports.store') }}">@include('ports._form')</form>
@stop

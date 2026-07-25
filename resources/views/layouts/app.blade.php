@extends('layouts.bootstrap5')

@section('title', trim($__env->yieldContent('page_title', 'Supply Chain Intelligence')))

@section('css')
<link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}">
@stack('styles')
@stop

@section('js')
@stack('scripts')
@stop

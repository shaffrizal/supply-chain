@extends('adminlte::page')

@section('title', 'News API')

@section('content_header')
<h1>Berita Supply Chain</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        @if(isset($data['articles']) && count($data['articles']) > 0)

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Sumber</th>
                        <th>Tanggal</th>
                        <th>Link</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($data['articles'] as $news)

                    <tr>

                        <td>{{ $news['title'] }}</td>

                        <td>{{ $news['source']['name'] }}</td>

                        <td>{{ \Carbon\Carbon::parse($news['publishedAt'])->format('d-m-Y H:i') }}</td>

                        <td>
                            <a href="{{ $news['url'] }}" target="_blank" class="btn btn-primary btn-sm">
                                Baca
                            </a>
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        @else

            <div class="alert alert-warning">
                Tidak ada berita ditemukan.
            </div>

        @endif

    </div>
</div>

@stop
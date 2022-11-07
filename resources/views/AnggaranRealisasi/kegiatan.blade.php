@extends('adminlte::page')

@section('title', $judul)

@section('content_header')
    @foreach ($css_files as $css_file)
        <link rel="stylesheet" href="{{ $css_file }}">
    @endforeach
@stop

@section('content')
    @if(session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif
    <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.importkegiatan')}}">Import Kegiatan</a>

    <div style="padding: 20px">
        {!! $output !!}
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
@section('js')
    @foreach ($js_files as $js_file)
        <script src="{{ $js_file }}"></script>
    @endforeach
    <script>
        if (typeof $ !== 'undefined') {
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });
        }
    </script>
@stop


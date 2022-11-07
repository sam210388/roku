@extends('adminlte::page')

@section('title', $judul)

@section('content_header')
    @foreach ($css_files as $css_file)
        <link rel="stylesheet" href="{{ $css_file }}">
    @endforeach
@stop

@section('content')
    @if(session('rekapberhasil'))
        <div class="alert alert-success">
            {{session('rekapberhasil')}}
        </div>
    @endif
    @if(session('alokasiberhasil'))
        <div class="alert alert-success">
            {{session('alokasiberhasil')}}
        </div>
    @endif
    <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.tampil-bagian')}}">Kembali Ke Daftar Bagian</a>
    <a class="btn btn-info text-white btn-sm">{{$infosetjen}}</a>
    <a class="btn btn-info text-white btn-sm">{{$infodewan}}</a>
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

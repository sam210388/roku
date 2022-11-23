@extends('adminlte::page')

@section('title', $judul)

@section('content_header')
    @foreach ($css_files as $css_file)
        <link rel="stylesheet" href="{{ $css_file }}">
    @endforeach
@stop

@section('content')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <p>Realisasi Setjen (Terbit SP2D) :  {{$info['realisasisetjenselesai']}} </p>
                <p>Realisasi Setjen Dalam Proses  :  {{$info['realisasisetjenproses']}} </p>
                <p>Realisasi Dewan (Terbit SP2D) :  {{$info['realisasidewanselesai']}} </p>
                <p>Realisasi Dewan Dalam Proses  :  {{$info['realisasidewanproses']}} </p>
                <p>Last Update : {{$info['waktuupdate']}}</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{route('anggaran.importrealisasi')}}" class="small-box-footer">Import Realisasi  <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div style="padding: 20px">
        {!! $output !!}
    </div>
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

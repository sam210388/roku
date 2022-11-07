@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <p>Revisi DIPA Setjen Terakhir :  {{$maxrevisidipasetjen}} </p>
                    <p>Revisi POK Setjen Terakhir :  {{$maxrevisipoksetjen}} </p>
                    <p>Revisi DIPA Dewan Terakhir :  {{$maxrevisidipadewan}} </p>
                    <p>Revisi POK Dewan Terakhir :  {{$maxrevisipokdewan}} </p>
                    <p>Last Update : {{$waktuupdate}}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>

                <a href="{{route('anggaran.tampil-refstatus')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
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
    </div>


@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop

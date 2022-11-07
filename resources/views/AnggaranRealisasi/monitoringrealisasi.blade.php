@extends('adminlte::page')

@section('title', 'Monitoring Realisasi')

@section('content_header')
    <h1>Monitoring Realisasi</h1>
@stop

@section('content')

    @if(session('gagal'))
        <div class="alert alert-danger">
            {{session('gagal')}}
        </div>
    @endif

    <form action="{{ route('anggaran.aksirekapmonitoringrealisasi') }}" method="post" autocomplete="off" onsubmit="cekisian()">
        @csrf
        <div class="card-body">

            <label class="radio-inline">
                <input type="radio" name="periodesasi" value="1" checked>  Bulan Ini
            </label>
            &nbsp;
            <label class="radio-inline">
                <input type="radio" name="periodesasi" value="2">  Sampai Dengan Bulan Ini
            </label>
            <br>
            <br>
            @section('plugins.Select2',true)
            <x-adminlte-select2 name="bulan" id="bulan" data-placeholder="Pilih Bulan" required>
                <x-slot name="appendSlot">
                    <div class="input-group-text">
                        <i class="fas fa-search"></i>
                    </div>
                </x-slot>
                <option value="">Pilih Bulan</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </x-adminlte-select2>
            <x-adminlte-select2 name="satker" id="bulan" data-placeholder="Pilih Satker" required>
                <x-slot name="appendSlot">
                    <div class="input-group-text">
                        <i class="fas fa-search"></i>
                    </div>
                </x-slot>
                <option value="">Pilih Satker</option>
                <option value="001012">Setjen</option>
                <option value="001030">Dewan</option>
                <option value="lembaga">Lembaga</option>
            </x-adminlte-select2>
        </div>
        <!-- /.card-body -->

        <div class="card-footer">
            <button type=submit class="btn btn-block" id="submit">
                <span class="fas fa-sign-in-alt"></span>
                Rekap Data
            </button>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Hi!'); </script>

@stop

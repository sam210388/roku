@extends('adminlte::page')

@section('title', $judul)

@section('content_header')
    <h1>Penilaian IKPA</h1>
@stop

@section('content')

    @if(session('gagal'))
        <div class="alert alert-danger">
            {{session('gagal')}}
        </div>
    @endif

    <form action="{{ route('ikpa.aksirekapnilaiikpa') }}" method="post" autocomplete="off">
        @csrf
        <div class="card-body">

            <label class="radio-inline">
                <input type="radio" name="periode" value="1" checked>  Triwulan I
            </label>
            &nbsp;
            <label class="radio-inline">
                <input type="radio" name="periode" value="2">  Triwulan II
            </label>
            &nbsp;
            <label class="radio-inline">
                <input type="radio" name="periode" value="3">  Triwulan III
            </label>
            &nbsp;
            <label class="radio-inline">
                <input type="radio" name="periode" value="4">  Triwulan IV
            </label>
            <br>
            <br>
            @section('plugins.Select2',true)
            <x-adminlte-select2 name="satker" data-placeholder="Pilih Satker">
                <x-slot name="appendSlot">
                    <div class="input-group-text">
                        <i class="fas fa-search"></i>
                    </div>
                </x-slot>
                <option>Pilih Satker</option>
                <option value="001012">Setjen</option>
                <option value="001030">Dewan</option>
            </x-adminlte-select2>

        </div>
        <!-- /.card-body -->

        <div class="card-footer">
            <button type=submit class="btn btn-block">
                <span class="fas fa-sign-in-alt"></span>
                Rekap Data
            </button>
        </div>
    </form>
@stop



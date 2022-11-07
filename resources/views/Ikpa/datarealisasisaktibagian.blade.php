@extends('adminlte::page')

@section('title', $judul)

@section('content')
    <br>
    <div class="row">
        <a class="btn btn-danger text-white btn-sm" href="{{url('ikpa/tampilrealisasibagian').'/'.$idbiro.'/'.$satker}}">Kembali</a>
        &nbsp;
        &nbsp;
        <a class="btn btn-success text-white btn-sm" href="{{url('ikpa/datasaktibagian').'/'.$satker.'/'.$idbagian}}">Export</a>
    </div>

    <input type="hidden" id="idbagian" value="{{$idbagian}}">
    <input type="hidden" id="satker" value="{{$satker}}">
    <br>
    <br>
    <p>Rincian Realisasi SAKTI untuk Bagian {{$uraianbagian}} untuk Satker {{$satker}}</p>
    <table id="realisasisaktibagian-datatable" class="table table-bordered realisasisaktibagian-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>No SPP</th>
            <th>No SP2D</th>
            <th>Tgl SP2D</th>
            <th>Pengenal</th>
            <th>Uraian</th>
            <th>Nilai Rupiah</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>No SPP</th>
            <th>No SP2D</th>
            <th>Tgl SP2D</th>
            <th>Pengenal</th>
            <th>Uraian</th>
            <th>Nilai Rupiah</th>
        </tr>
        </tfoot>
    </table>


@stop
@section('js')
    <script type="text/javascript">
        $(document).ready(function () {
            // Setup - add a text input to each footer cell
            $('#realisasisaktibagian-datatable tfoot th').each( function (i) {
                var title = $('#realisasisaktibagian-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });
            var idbagian = document.getElementById('idbagian').value;
            var satker = document.getElementById('satker').value;

            var table = $('.realisasisaktibagian-datatable').DataTable({
                fixedColumn:true,
                scrollX:"100%",
                autoWidth:true,
                processing: true,
                serverSide: true,

                ajax:"{{url('ikpa/getdatasaktibagian')}}"+'/'+idbagian+'/'+satker,
                columns: [
                    {data: 'no_spp', name: 'no_spp'},
                    {data: 'no_sp2d', name: 'no_sp2d'},
                    {data:'tgl_sp2d',name:'tgl_sp2d'},
                    {data: 'pengenal', name: 'pengenal'},
                    {data: 'uraian', name: 'uraian'},
                    {data: 'nilairupiah', name: 'nilairupiah'},
                ],
            });
            // Filter event handler
            $( table.table().container() ).on( 'keyup', 'tfoot input', function () {
                table
                    .column( $(this).data('index') )
                    .search( this.value )
                    .draw();
            } );
        });
    </script>
@stop

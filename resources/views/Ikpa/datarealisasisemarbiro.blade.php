@extends('adminlte::page')

@section('title', $judul)

@section('content')
    <br>
    <div class="row>">
        <a class="btn btn-danger text-white btn-sm" href="{{route('ikpa.tampilrealisasibiro')}}">Kembali</a>
        &nbsp
        &nbsp
        <a class="btn btn-success text-white btn-sm" href="{{url('ikpa/datasemarbiro').'/'.$satker.'/'.$idbiro}}">Export</a>
    </div>
    <input type="hidden" id="idbiro" value="{{$idbiro}}">
    <input type="hidden" id="satker" value="{{$satker}}">
    <br>
    <br>
    <table id="realisasisemarbiro-datatable" class="table table-bordered realisasisemarbiro-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>Tanggal Kwitansi</th>
            <th>No SPP</th>
            <th>No SPBy</th>
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
            $('#realisasisemarbiro-datatable tfoot th').each( function (i) {
                var title = $('#realisasisemarbiro-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });
            var idbiro = document.getElementById('idbiro').value;
            var satker = document.getElementById('satker').value;

            var table = $('.realisasisemarbiro-datatable').DataTable({
                fixedColumn:true,
                scrollX:"100%",
                autoWidth:true,
                processing: true,
                serverSide: true,

                ajax:"{{url('ikpa/datasemarperbiro')}}"+'/'+idbiro+'/'+satker,
                columns: [
                    {data:'tanggal_kwitansi_karwas',name:'tanggal_kwitansi_karwas'},
                    {data: 'no_spp', name: 'no_spp'},
                    {data: 'no_spby', name: 'no_spby'},
                    {data: 'pengenal', name: 'pengenal'},
                    {data: 'uraian_pekerjaan', name: 'uraian_pekerjaan'},
                    {data: 'nilai_tagihan', name: 'nilai_tagihan'},
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

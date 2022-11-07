@extends('adminlte::page')

@section('title', $judul)

@section('content')
    @if(session('berhasil'))
        <div class="alert alert-success">
            {{session('berhasil')}}
        </div>
    @endif
    @if(session('rekapberhasil'))
        <div class="alert alert-success">
            {{session('rekapberhasil')}}
        </div>
    @endif
    @if(session('gagal'))
        <div class="alert alert-danger">
            {{session('gagal')}}
        </div>
    @endif
    <br>
    <p>Realisasi Per Bagian Untuk Biro {{$uraianbiro}} pada Satker {{$kdsatker}}</p>
    <input type="hidden" id="idbiro" value="{{$idbiro}}">
    <input type="hidden" id="satker" value="{{$kdsatker}}">
    <table id="realisasibagian-datatable" class="table table-bordered realisasibagian-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>Bagian</th>
            <th>Anggaran</th>
            <th>Realisasi SAKTI</th>
            <th>Prosentase SAKTI</th>
            <th>Realisasi SEMAR</th>
            <th>Prosentase SEMAR</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>Bagian</th>
            <th>Anggaran</th>
            <th>Realisasi SAKTI</th>
            <th>Prosentase SAKTI</th>
            <th>Realisasi SEMAR</th>
            <th>Prosentase SEMAR</th>
        </tr>
        </tfoot>
    </table>


@stop
@section('js')
    <script type="text/javascript">
        $(function () {
            // Setup - add a text input to each footer cell
            $('#realisasibagian-datatable tfoot th').each( function (i) {
                var title = $('#realisasibagian-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });

            var idbiro = document.getElementById('idbiro').value;
            var satker = document.getElementById('satker').value;

            var table = $('.realisasibagian-datatable').DataTable({
                fixedColumn:true,
                scrollX:"100%",
                autoWidth:true,
                processing: true,
                serverSide: true,
                ajax:"{{url('ikpa/getdatarealisasibagian')}}"+'/'+idbiro+'/'+satker,
                columns: [
                    {data: 'bagian', name: 'bagian'},
                    {data:'diparevisi',name:'diparevisi'},
                    {data: 'realisasisakti', name: 'realisasisakti'},
                    {data: 'prosentasesakti', name: 'prosentasesakti'},
                    {data: 'realisasisemar', name: 'realisasisemar'},
                    {data: 'prosentasesemar', name: 'prosentasesemar'},
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

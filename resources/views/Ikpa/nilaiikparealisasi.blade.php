@extends('adminlte::page')

@section('title', $judul)

@section('content')
    <input type="hidden" id="satker" value="{{$satker}}">
    <input type="hidden" id="periode" value="{{$periode}}">
    <p>Nilai IKPA Per Bagian Satker {{$satker}} Untuk Periode {{$uraianperiode}}  </p>
    <table id="ikpabagian-datatable" class="table table-bordered ikpabagian-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>Bagian</th>
            <th>TW 1</th>
            <th>TW 2</th>
            <th>TW 3</th>
            <th>Tw 4</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>Bagian</th>
            <th>TW 1</th>
            <th>TW 2</th>
            <th>TW 3</th>
            <th>Tw 4</th>
        </tr>
        </tfoot>
    </table>


@stop
@section('js')
    <script type="text/javascript">
        $(function () {
            // Setup - add a text input to each footer cell
            $('#ikpabagian-datatable tfoot th').each( function (i) {
                var title = $('#ikpabagian-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });


            var satker = document.getElementById('satker').value;
            var periode = document.getElementById('periode').value;

            var table = $('.ikpabagian-datatable').DataTable({
                fixedColumn:true,
                scrollX:"100%",
                autoWidth:true,
                processing: true,
                serverSide: true,
                ajax:"{{url('ikpa/getnilaiikparealisasi')}}"+'/'+satker+'/'+periode,
                columns: [
                    {data: 'bagian', name: 'bagian'},
                    {data:'tw1',name:'tw1'},
                    {data: 'tw2', name: 'tw2'},
                    {data: 'tw3', name: 'tw3'},
                    {data: 'tw4', name: 'tw4'},
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

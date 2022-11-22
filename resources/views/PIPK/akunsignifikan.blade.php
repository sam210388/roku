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
    <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.importrefstatus')}}">Import Referensi Status</a>
    {{--
        <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.cekstatusimport')}}">Cek Status Import</a>
    <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.importdataangseluruh')}}">Import Seluruh Data Anggaran</a>
    <a class="btn btn-info text-white btn-sm" href="{{route('anggaran.rekapseluruhanggaran')}}">Rekap Seluruh Anggaran</a>

    --}}

    <br>
    <br>
    <table id="refstatus-datatable" class="table table-bordered refstatus-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>ID Ref</th>
            <th>Kode Satker</th>
            <th>Kode History</th>
            <th>Jenis Revisi</th>
            <th>Revisi Ke</th>
            <th>Tanggal Revisi</th>
            <th>Pagu Belanja</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>ID Ref</th>
            <th>Kode Satker</th>
            <th>Kode History</th>
            <th>Jenis Revisi</th>
            <th>Revisi Ke</th>
            <th>Tanggal Revisi</th>
            <th>Pagu Belanja</th>
            <th>Action</th>
        </tr>
        </tfoot>
    </table>


@stop
@section('js')
    <script type="text/javascript">
        $(function () {
            // Setup - add a text input to each footer cell
            $('#refstatus-datatable tfoot th').each( function (i) {
                var title = $('#refstatus-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });
            var table = $('.refstatus-datatable').DataTable({
                fixedColumn:true,
                scrollX:"100%",
                autoWidth:true,
                processing: true,
                serverSide: true,
                ajax:"{{ route('anggaran.datarefstatus') }}",
                columns: [
                    {data: 'idrefstatus', name: 'idrefstatus'},
                    {data: 'kdsatker', name: 'kdsatker'},
                    {data: 'kd_sts_history', name: 'kd_sts_history'},
                    {data: 'jenis_revisi', name: 'jenis_revisi'},
                    {data: 'revisi_ke', name: 'revisi_ke'},
                    {data: 'tgl_revisi', name: 'tgl_revisi'},
                    {data: 'pagu_belanja', name: 'pagu_belanja'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
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

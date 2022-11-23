@extends('adminlte::page')

@section('title', $judul)
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

    <a class="btn btn-success" href="javascript:void(0)" id="tambahakunsignifikan"> Tambah Data</a>
    <br>
    <br>
    <table id="refstatus-datatable" class="table table-bordered refstatus-datatable" style="width: 100%; word-break: break-all">
        <thead>
        <tr>
            <th>No</th>
            <th>ID</th>
            <th>Tahun Anggaran</th>
            <th>Kode Akun</th>
            <th>Deskripsi</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
            <th>No</th>
            <th>ID</th>
            <th>Tahun Anggaran</th>
            <th>Kode Akun</th>
            <th>Deskripsi</th>
            <th>Action</th>
        </tr>
        </tfoot>
    </table>
    <div class="modal fade" id="ajaxModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading"></h4>
                </div>
                <div class="modal-body">
                    <form id="formakunsignifikan" name="CustomerForm" class="form-horizontal">
                        <input type="hidden" name="idakunsignifikan" id="idakunsignifikan">
                        @section('plugins.Select2',true)
                        <x-adminlte-select2 name="kodeakun" data-placeholder="Pilih Kode AKun" required>
                            <x-slot name="appendSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </div>
                            </x-slot>
                            <option value="">Pilih Kode Akun</option>
                            @foreach($dataakun as $data)
                                <option value="{{ $data->kode }}">{{ $data->deskripsi }}</option>
                            @endforeach
                        </x-adminlte-select2>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Deskripsi</label>
                            <div class="col-sm-12">
                                <textarea id="deskripsi" name="deskripsi" required="" placeholder="Deskripsi" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
@section('js')
    <script type="text/javascript">

        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Setup - add a text input to each footer cell
            $('#refstatus-datatable tfoot th').each( function (i) {
                var title = $('#refstatus-datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" placeholder="'+title+'" data-index="'+i+'" />' ).css(
                    {"width":"5%"},
                );
            });
            var table = $('.refstatus-datatable').DataTable({
                scrollX:"100%",
                processing: true,
                serverSide: true,
                ajax:"{{ route('pipk.akunsignifikan.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                    {data: 'id', name: 'id'},
                    {data: 'tahunanggaran', name: 'tahunanggaran'},
                    {data: 'kodeakun', name: 'kodeakun'},
                    {data: 'deskripsi', name: 'deskripsi'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                ]
            });
            // Filter event handler
            $( table.table().container() ).on( 'keyup', 'tfoot input', function () {
                table
                    .column( $(this).data('index') )
                    .search( this.value )
                    .draw();
            } );


            $('#tambahakunsignifikan').click(function () {
                $('#saveBtn').val("tambahakunsignifikan");
                $('#idakunsignifikan').val('');
                $('#formakunsignifikan').trigger("reset");
                $('#modelHeading').html("Tambah Akun Signifikan");
                $('#ajaxModel').modal('show');
            });


            $('body').on('click', '.editakunsignifikan', function () {
                var idakunsignifikan = $(this).data('id');
                $.get("{{ route('pipk.akunsignifikan.index') }}" +'/' + idakunsignifikan +'/edit', function (data) {
                    $('#modelHeading').html("Edit Akun Signifikan");
                    $('#saveBtn').val("edit-akunsignifikan");
                    $('#ajaxModel').modal('show');
                    $('#idakunsignifikan').val(data.id);
                    $('#kodeakun').val(data.kodeakun);
                    $('#deskripsi').val(data.deskripsi);
                })
            });

            $('#saveBtn').click(function (e) {
                e.preventDefault();
                $(this).html('Sending..');

                $.ajax({
                    data: $('#formakunsignifikan').serialize(),
                    url: "{{ route('pipk.akunsignifikan.store') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        $('#formakunsignifikan').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        $('#saveBtn').html('Simpan');
                    }
                });
            });

            $('body').on('click', '.deleteakunsignifikan', function () {
                var idakunsignifikan = $(this).data("id");
                if(confirm("Apakah Anda Yakin Akan Menghapus Data ?")){
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('pipk.akunsignifikan.store') }}"+'/'+idakunsignifikan,
                        success: function (data) {
                            table.draw();
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                }
            });

        });

    </script>
@stop

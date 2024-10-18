@extends('layouts.admin_master')
@section('content')




<div class="card mb-5">
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        User Log List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0" id="logs_table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Email</th>
                        <th>Created_at</th>

                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user_logs as $row)



                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->created_at }}</td>
                        <td>


                            <a href="{{ 'del-log/'.$row->id }}" class="btn btn-sm btn-danger" id="del_log">Delete</a>

                            <a href="#" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#myModal" id="btn" data-id="{{ $row->id }}">DeleteConfirm</a>
                            <div class="modal" id="myModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <!--                                        Modal header -->
                                        <div class="modal-header">
                                            <h4 class="modal-title">Attention!</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>

                                        <!--                                        Modal body -->
                                        <div class="modal-body">
                                            Are you sure that you want to delete the selected record? <br>
<!--                                            <input type="text" name="bookId" id="bookId" value=""/>-->
                                        </div>

                                        <!--                                        Modal footer -->
                                        <div class="modal-footer">
                                            <a href="#" class="btn btn-sm btn-danger" id="del_log">Delete</a>

                                        </div>

                                    </div>
                                </div>
                            </div>




                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('script')
<link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />

<script>



    $('#dataTable').DataTable({
        columnDefs: [
            {bSortable: false, targets: [3]}
        ],
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, ':visible']

                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, ':visible']
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    },
                    columns: [0, 1, 2]
                }
            },
            'colvis'

        ],
    });</script>


<script>


    // $(document).on('click', '#del_log', function () { // <-- changes
    //     $(this).closest('tr').remove();
    //     return false;
    // });
    $(document).on("click", ".btn", function () {
        var myBookId = $(this).data('id');
        //$(".modal-body #bookId").val(myBookId);
        $(".modal-footer #del_log").attr('href', 'del-log/' + myBookId.toString());


    });

</script>


@endsection

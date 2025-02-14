


<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Group Approvals</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Group Approvals</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
       
      <div class="card">
              
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th width="80" class="">Sl No</th>
                    <th>Group Name</th>
                    <th>Type</th>
                    <th>Mobile No</th>
                    <th>Purpose</th>
                    <th>Created Date</th>
                    <th width="90">Action</th>
                  </tr>
                  </thead>
                  <tbody>
                   
                  </tbody>
                 
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
      </div><!--/. container-fluid -->
    </section>
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="actionModalLabel"></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <!-- Dynamic content will be inserted here -->
                  <p id="actionModalContent"></p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="modalActionButton"></button>
              </div>
          </div>
      </div>
    </div>


    <div class="modal fade" id="viewEditModal" tabindex="-1" aria-labelledby="viewEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewEditModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <!-- Dynamic content loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveChangesButton" style="display: none;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
   
    $(document).ready(function(){
        var table = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('/admin/group/grouplist') }}", // Updated URL
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Ensure CSRF token is included
                }
            },
            columns: [
                { 
                    data: null, 
                    name: 'sl_no',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1; 
                    },
                    orderable: false, 
                    searchable: false,
                    class:"text-center"
                },
                { data: 'group_name', name: 'group_name' },
                { data: 'type', name: 'type' },
                { data: 'mobile_number', name: 'mobile_number' },
                { data: 'purpose', name: 'purpose' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

    });

    function handleAction(itemId,action) {
        
        $.ajax({
            url: '/admin/group/' + itemId,  
            method: 'GET',
            success: function(data) {
                // console.log(action);
                switch(action) {
                    case 'view':
                        $('#viewEditModalLabel').text('Group Details');
                            $('#modalBodyContent').html(data);
                        $('#viewEditModal').modal('show');
                        break;
                    case 'reject':
                        rejectGroup(itemId);
                        break;
                    case 'approve':
                        approveGroup(itemId);
                        break;
                }
            }
        });
    }


// Function for editing a group
    function editGroup(groupName, type, mobile) {
        $('#actionModalLabel').text('Edit Group');
        $('#modalBodyContent').html(`
            <form id="editForm">
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" class="form-control" value="${groupName}">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <input type="text" class="form-control" value="${type}">
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" class="form-control" value="${mobile}">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        `);
        $('#actionModal').modal('show');
    }

    // Function for rejecting a group
    function rejectGroup(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to reject this group?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request to update the group status to rejected in the database
                $.ajax({
                    url: '/admin/group/reject/' + id,  // Ensure the correct route for reject action
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'  // Include CSRF token
                    },
                    success: function(response) {
                        // Show success message
                        Swal.fire(
                            'Rejected!',
                            'The group has been rejected.',
                            'success'
                        );
                       
                        $('#example1').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error!',
                            'There was an issue rejecting the group.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function approveGroup(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to approve this group?",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX request to update the group status to rejected in the database
                $.ajax({
                    url: '/admin/group/approve/' + id,  // Ensure the correct route for reject action
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'  // Include CSRF token
                    },
                    success: function(response) {
                        // Show success message
                        Swal.fire(
                            'Approved!',
                            'The group has been approved.',
                            'success'
                        );
                        // $('#example1').DataTable().clear().draw();
                        $('#example1').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error!',
                            'There was an issue approving the group.',
                            'error'
                        );
                    }
                });
            }
        });
    }


    
    </script>
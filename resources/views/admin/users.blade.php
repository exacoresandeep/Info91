


<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Users</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
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
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Mobile No</th>
                  <th>State</th>
                  <th>District</th>
                  <th>Pincode</th>
                  <th>Created</th>
                  <th>Status</th>
                  <th width="60">Action</th>
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
                  {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="saveChangesButton" style="display: none;">Save Changes</button> --}}
              </div>
          </div>
      </div>
  </div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        
 $(document).ready(function () {
    
    var table = $('#example1').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('/admin/userList') }}", // Updated URL
            method: 'POST',
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
            { data: 'user_id', name: 'user_id' },
            { data: 'name', name: 'name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'state_name', name: 'state_name' },
            { data: 'district_name', name: 'district_name' },
            { data: 'pincode', name: 'pincode' },
            { data: 'created_at', name: 'created_at' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
     
      
    

    document.addEventListener("click", function (event) {
    if (event.target && event.target.id === "saveStatusBtn") {
        let userIdElement = document.getElementById("user_id");
        let statusElement = document.getElementById("edit_status");

        if (!userIdElement || !statusElement) {
            console.error("User ID or status field not found.");
            return;
        }

        let userId = userIdElement.value;
        let status = statusElement.value;

        fetch("/admin/updateUserStatus", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            },
            body: JSON.stringify({ user_id: userId, status: status }),
        })
        .then(response => response.json())
        .then(data => {
            $('#viewEditModal').modal('hide');
            $('#example1').DataTable().ajax.reload(null, false);
            if (data.success) {
                Swal.fire({
                    title: "Success!",
                    text: "User status updated successfully!",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            } else {
                
                Swal.fire({
                    title: "Error!",
                    text: data.message,
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire({
                title: "Oops!",
                text: "Something went wrong. Try again!",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    }
});

});

  function handleAction(itemId,action) {
      
      $.ajax({
          url: '/admin/viewUser/' + itemId,  
          method: 'GET',
          success: function(data) {
              // console.log(action);
              switch(action) {
                  case 'view':
                      $('#viewEditModalLabel').text('User Details');
                          $('#modalBodyContent').html(data);
                      $('#viewEditModal').modal('show');
                      break;
                  
                  case 'ban':
                      banUser(itemId);
                      break;
                  case 'ban':
                      unbanUser(itemId);
                      break;
              }
          }
      });
  }



  
  </script>
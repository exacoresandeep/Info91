<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Plans</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Plans</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="add-button-container">
          <button class="btn btn-primary btn-success" data-toggle="modal" data-target="#addPlanModal">ADD</button>
      </div>
      <br>
      <div class="card">
                
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th width="80" class="">Sl No</th>
                    <th>Plan Name</th>
                    <th>Amount</th>
                    <th>Days</th>
                    <th>Tax</th>
                    <th>Member Limits</th>
                    <th>Status</th>
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
  
  <div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="addPlanModalLabel">Add Plan</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="addPlanForm">
                      @csrf
                      <div class="form-group">
                          <label for="plan_name">Plan Name</label>
                          <input type="text" class="form-control" id="plan_name" name="plan_name" required>
                      </div>
                      <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration Days</label>
                        <input type="number" class="form-control" id="duration" name="duration" required>
                    </div>
                    <div class="form-group">
                        <label for="tax">Tax</label>
                        <input type="number" class="form-control" id="tax" name="tax" required>
                    </div>
                    <div class="form-group">
                        <label for="total_members">Member Limit</label>
                        <input type="number" class="form-control" id="total_members" name="total_members" required>
                    </div>
                      <div class="form-group">
                          <label for="status">Status</label>
                          <select class="form-control" id="status" name="status" required>
                              <option value="1">Active</option>
                              <option value="0">Inactive</option>
                          </select>
                      </div>
                  </form>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="savePlanButton">Save</button>
              </div>
          </div>
      </div>
  </div>
  
  <div class="modal fade" id="viewEditModal" tabindex="-1" aria-labelledby="viewEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md">
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
          $('#viewEditModal').on('show.bs.modal', function () {
              $(this).attr('aria-hidden', 'false'); // Ensure aria-hidden is false when modal is shown
          });
  
          $('#viewEditModal').on('hidden.bs.modal', function () {
              $(this).attr('aria-hidden', 'true'); // Set aria-hidden to true when modal is hidden
          });
          var table = $('#example1').DataTable({
              processing: true,
              serverSide: true,
              ajax: {
                  url: "{{ url('/admin/planList') }}", // Updated URL
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
                  { data: 'plan_name', name: 'plan_name' },
                  { data: 'amount', name: 'amount' },
                  { data: 'duration', name: 'duration' },
                  { data: 'tax', name: 'tax' },
                  { data: 'total_members', name: 'total_members' },
                  { data: 'status', name: 'status' },
                  { data: 'created_at', name: 'created_at' },
                  { data: 'action', name: 'action', orderable: false, searchable: false }
              ]
          });
  
      });
  
      $('#savePlanButton').click(function () {
          var formData = $('#addPlanForm').serialize();
  
          $.ajax({
              url: "{{ url('/admin/addPlan') }}", // Update the route as necessary
              type: "POST",
              data: formData,
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function (response) {
                  if (response.success) {
                      Swal.fire('Success', response.message, 'success');
                      $('#addPlanModal').modal('hide');
                      $('#example1').DataTable().ajax.reload(); // Reload the DataTable
                  } else {
                      Swal.fire('Error', response.message, 'error');
                  }
              },
              error: function (error) {
                  Swal.fire('Error', 'Something went wrong!', 'error');
              }
          });
      });
  
      function handleAction(itemId, action) {
          $.ajax({
              url: '/admin/viewPlan/' + itemId,
              method: 'GET',
              success: function(response) {
                  if (action === 'view') {
                      $('#viewEditModalLabel').text('View Plan Details');
                      $('#modalBodyContent').html(response.data);
                      $('#viewEditModal').modal('show');
                  }
              },
              error: function() {
                  Swal.fire('Error', 'Could not fetch plan details.', 'error');
              }
          });
      }
      function editPlan(itemId) {
          // Send an AJAX request to fetch the plan details
          $.ajax({
              url: '/admin/editPlan/' + itemId, // Adjust the route as needed
              method: 'GET',
              success: function(response) {
                  if (response.success) {
                      // Populate the modal with data from the response
                      $('#viewEditModalLabel').text('Edit Plan');
                      
                      // Fill input fields with the fetched data
                      $('#modalBodyContent').html(`
                          <form id="editPlanForm">
                              @csrf
                                <div class="form-group">
                                    <label for="edit_plan_name">Plan Name</label>
                                    <input type="text" class="form-control" id="edit_plan_name" name="plan_name" value="${response.data.plan_name}" required>
                                </div>
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount"  value="${response.data.amount}" required>
                                </div>
                                <div class="form-group">
                                    <label for="duration">Duration Days</label>
                                    <input type="number" class="form-control" id="duration" name="duration"  value="${response.data.duration}" required>
                                </div>
                                <div class="form-group">
                                    <label for="tax">Tax</label>
                                    <input type="number" class="form-control" id="tax" name="tax"  value="${response.data.tax}" required>
                                </div>
                                <div class="form-group">
                                    <label for="total_members">Member Limit</label>
                                    <input type="number" class="form-control" id="total_members" name="total_members"  value="${response.data.total_members}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_status">Status</label>
                                    <select class="form-control" id="edit_status" name="status" required>
                                        <option value="1" ${response.data.status == 1 ? 'selected' : ''}>Active</option>
                                        <option value="0" ${response.data.status == 0 ? 'selected' : ''}>Inactive</option>
                                    </select>
                                </div>
                          </form>
                      `);
                      // Show the Save Changes button
                    $('#saveChangesButton').show();

                    // Bind the Save Changes button to update the category
                    $('#saveChangesButton').off('click').on('click', function() {
                        updatePlan(itemId);
                    });

                    // Show the modal
                    $('#viewEditModal').modal('show');
                    } else {
                    Swal.fire('Error', response.message, 'error');
                    }
              }
          });
      } 

    function updatePlan(itemId) {
        // Collect form data
        var formData = $('#editPlanForm').serialize();

        // Send AJAX request to update the category
        $.ajax({
            url: '/admin/updatePlan/' + itemId, // Adjust the route as necessary
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#viewEditModal').modal('hide');
                    $('#example1').DataTable().ajax.reload(); // Reload the DataTable
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to update Plan', 'error');
            }
        });
    }

    function deletePlan(itemId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/deletePlan/' + itemId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $('#example1').DataTable().ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'Could not delete category.', 'error');
                    }
                });
            }
        });
    }
  </script>
  
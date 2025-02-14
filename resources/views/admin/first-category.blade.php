


<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">First Category</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">First Category</li>
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
            <button class="btn btn-primary btn-success" data-toggle="modal" data-target="#addCategoryModal">ADD</button>
        </div>
        <br>
      <div class="card">
              
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th width="80" class="">Sl No</th>
                    <th>Category Name</th>
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

    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add First Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        @csrf
                        <div class="form-group">
                            <label for="first_category_name">Category Name</label>
                            <input type="text" class="form-control" id="first_category_name" name="first_category_name" required>
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
                    <button type="button" class="btn btn-primary" id="saveCategoryButton">Save</button>
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
                url: "{{ url('/admin/firstCateoryList') }}", // Updated URL
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
                { data: 'first_category_name', name: 'first_category_name' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

    });
    $('#saveCategoryButton').click(function () {
        var formData = $('#addCategoryForm').serialize();

        $.ajax({
            url: "{{ url('/admin/addFirstCategory') }}", // Update the route as necessary
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#addCategoryModal').modal('hide');
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
            url: '/admin/viewFirstCategory/' + itemId,
            method: 'GET',
            success: function(response) {
                if (action === 'view') {
                    $('#viewEditModalLabel').text('View Category Details');
                    $('#modalBodyContent').html(response.data);
                    $('#viewEditModal').modal('show');
                }
            },
            error: function() {
                Swal.fire('Error', 'Could not fetch category details.', 'error');
            }
        });
    }
    function editFirstCategory(itemId) {
        // Send an AJAX request to fetch the category details
        $.ajax({
            url: '/admin/editFirstCategory/' + itemId, // Adjust the route as needed
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Populate the modal with data from the response
                    $('#viewEditModalLabel').text('Edit Category');
                    
                    // Fill input fields with the fetched data
                    $('#modalBodyContent').html(`
                        <form id="editCategoryForm">
                            @csrf
                            <div class="form-group">
                                <label for="edit_first_category_name">Category Name</label>
                                <input type="text" class="form-control" id="edit_first_category_name" name="first_category_name" value="${response.data.first_category_name}" required>
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
                        updateCategory(itemId);
                    });

                    // Show the modal
                    $('#viewEditModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to fetch category details', 'error');
            }
        });
    }

    function updateCategory(itemId) {
        // Collect form data
        var formData = $('#editCategoryForm').serialize();

        // Send AJAX request to update the category
        $.ajax({
            url: '/admin/updateFirstCategory/' + itemId, // Adjust the route as necessary
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
                Swal.fire('Error', 'Unable to update category', 'error');
            }
        });
    }

    function deleteFirstCategory(itemId) {
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
                    url: '/admin/deleteFirstCategory/' + itemId,
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
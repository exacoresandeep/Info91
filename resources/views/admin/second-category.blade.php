


<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Second Category</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Second Category</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
        
        <div class="add-button-container d-flex justify-content-between align-items-center">
            <div class="col-2 d-flex align-items-center p-0">
               
                <select class="form-control" id="firstCategorySelect">
                    <option value="">Select First Category</option>
                    <!-- Options will be loaded dynamically via AJAX -->
                </select>
            </div>
            <button 
                class="btn btn-success" 
                data-toggle="modal" 
                data-target="#addCategoryModal" 
                id="loadCreateForm">
                ADD
            </button>
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

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Second Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Form content will be loaded here -->
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
   
   $(document).ready(function () {
        // Populate First Category dropdown on page load
        $.ajax({
            url: "{{ route('admin.firstCategoryList') }}", // Adjust route
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let options = '<option value="">Select First Category</option>';
                    response.data.forEach(category => {
                        options += `<option value="${category.id}">${category.name}</option>`;
                    });
                    $('#firstCategorySelect').html(options);
                } else {
                    Swal.fire('Error', response.message || 'Could not load categories.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Failed to load categories.', 'error');
            }
        });


        // Update table based on selected First Category
        $('#firstCategorySelect').on('change', function () {
            const firstCategoryId = $(this).val();
            $('#example1').DataTable().ajax.url("{{ url('/admin/secondCategoryList') }}?first_category_id=" + firstCategoryId).load();
        });

        $('#loadCreateForm').on('click', function() {
            const url = "{{ route('admin.second-category.create') }}";
            $.get(url, function(data) {
                $('#modalContent').html(data);
            }).fail(function() {
                alert('Failed to load form. Please try again.');
            });
        });
        $('#viewEditModal').on('show.bs.modal', function () {
            $(this).attr('aria-hidden', 'false'); 
        });

        $('#viewEditModal').on('hidden.bs.modal', function () {
            $(this).attr('aria-hidden', 'true'); 
        });
        var table = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('/admin/secondCategoryList') }}", 
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
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
                { data: 'second_category_name', name: 'second_category_name' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

    });
    function saveCategoryButton() {
        
        var formData = {"second_category_name":$("#second_category_name").val(),
            "first_category_id":$("#first_category_id").val(),
            "status":"1"}

        $.ajax({
            url: "{{ url('/admin/addSecondCategory') }}", // Update the route as necessary
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
    }

    function handleAction(itemId, action) {
        $.ajax({
            url: '/admin/viewSecondCategory/' + itemId,
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
    function editSecondCategory(itemId) {
        $.ajax({
            url: '/admin/editSecondCategory/' + itemId, 
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    
                    $('#viewEditModalLabel').text('Edit Category');
                    
                    
                    $('#modalBodyContent').html(`
                        <form id="editCategoryForm">
                            @csrf
                            <div class="form-group">
                                <label for="edit_second_category_name">Category Name</label>
                                <input type="text" class="form-control" id="edit_second_category_name" name="second_category_name" value="${response.data.second_category_name}" required>
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

                    $('#saveChangesButton').show();

                    $('#saveChangesButton').off('click').on('click', function() {
                        updateCategory(itemId);
                    });
                    
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
        
        var formData = $('#editCategoryForm').serialize();

        $.ajax({
            url: '/admin/updateSecondCategory/' + itemId, 
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#viewEditModal').modal('hide');
                    $('#example1').DataTable().ajax.reload(); 
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to update category', 'error');
            }
        });
    }

    function deleteSecondCategory(itemId) {
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
                    url: '/admin/deleteSecondCategory/' + itemId,
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
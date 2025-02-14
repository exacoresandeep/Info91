


<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Districts</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Districts</li>
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
               
                <select class="form-control" id="stateSelect">
                    <option value="">Select State</option>
                    <!-- Options will be loaded dynamically via AJAX -->
                </select>
            </div>
            <div class="d-flex ml-auto">
                <button 
                    class="btn btn-warning mr-2" 
                    data-toggle="modal" 
                    data-target="#importModal" 
                    id="loadImportForm">
                    Import
                </button>
            
                <button 
                    class="btn btn-success" 
                    data-toggle="modal" 
                    data-target="#addDistrictModal" 
                    id="loadCreateForm">
                    ADD
                </button>
            </div>
        </div>

        <br>
        <div class="card">
              
              <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th width="80" class="">Sl No</th>
                        <th>District Name</th>
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
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="state">Select State</label>
                        <select name="state" id="state" class="form-control" required>
                            <option value="">-- Select State --</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                            @endforeach
                        </select>
                    </div>  
                    <div class="form-group">
                        <label for="importFile">Choose File</label>
                        <input type="file" name="importFile" id="importFile" class="form-control" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="ImportSubmitButton">Import</button>
                </div>
        </div>
    </div>
</div>

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

    <div class="modal fade" id="addDistrictModal" tabindex="-1" role="dialog" aria-labelledby="addDistrictModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDistrictModalLabel">Add District</h5>
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
    $('#ImportSubmitButton').on('click', function (e) {
        e.preventDefault();

        // Collect district ID and file
        let state_id = $('#state').val();
        let importFile = $('#importFile')[0].files[0];

        if (!state_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please select a state.',
            });
            return;
        }
        if (!importFile) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please select a file.',
            });
            return;
        }

        // Prepare FormData
        let formData = new FormData();
        formData.append('state_id', state_id);
        formData.append('importFile', importFile);

        // Send AJAX request
        $.ajax({
            url: "{{ route('admin.import-district') }}", // Replace with your actual route
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}" // Include CSRF token for Laravel
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                });
                $('#importModal').modal('hide');
                const stateId = $('#stateSelect option:selected').val();
                $('#example1').DataTable().ajax.url("{{ url('/admin/districtList') }}?state_id=" + stateId).load();
    
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON.message || 'An error occurred.',
                });
            }
        });
    });

        $.ajax({
            url: "{{ route('admin.state-list') }}", // Adjust route
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let options = '<option value="">Select State</option>';
                    response.data.forEach(state => {
                        options += `<option value="${state.id}">${state.name}</option>`;
                    });
                    $('#stateSelect').html(options);
                } else {
                    Swal.fire('Error', response.message || 'Could not load states.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Failed to load states.', 'error');
            }
        });
 
        $('#stateSelect').on('change', function () {
            const stateId = $(this).val();
            $('#example1').DataTable().ajax.url("{{ url('/admin/districtList') }}?state_id=" + stateId).load();
        });

        $('#loadCreateForm').on('click', function() {
            const url = "{{ route('admin.district.create') }}";
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
                url: "{{ url('/admin/districtList') }}", 
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
                { data: 'district_name', name: 'district_name' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

    });
    function saveCategoryButton() {
        
        var formData = {"district_name":$("#district_name").val(),
            "state_id":$("#state_id").val(),
            "status":"1"}

        $.ajax({
            url: "{{ url('/admin/addDistrict') }}", // Update the route as necessary
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#addDistrictModal').modal('hide');
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
            url: '/admin/viewDistrict/' + itemId,
            method: 'GET',
            success: function(response) {
                if (action === 'view') {
                    $('#viewEditModalLabel').text('View District Details');
                    $('#modalBodyContent').html(response.data);
                    $('#viewEditModal').modal('show');
                }
            },
            error: function() {
                Swal.fire('Error', 'Could not fetch district details.', 'error');
            }
        });
    }
    function editDistrict(itemId) {
        $.ajax({
            url: '/admin/editDistrict/' + itemId, 
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    
                    $('#viewEditModalLabel').text('Edit District');
                    
                    
                    $('#modalBodyContent').html(`
                        <form id="editDistrictForm">
                            @csrf
                            <div class="form-group">
                                <label for="edit_district_name">District Name</label>
                                <input type="text" class="form-control" id="edit_district_name" name="district_name" value="${response.data.district_name}" required>
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
                        updateDistrict(itemId);
                    });
                    
                    $('#viewEditModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Unable to fetch district details', 'error');
            }
        });
    }

    function updateDistrict(itemId) {
        
        var formData = $('#editDistrictForm').serialize();

        $.ajax({
            url: '/admin/updateDistrict/' + itemId, 
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

    function deleteDistrict(itemId) {
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
                    url: '/admin/deleteDistrict/' + itemId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $('#example1').DataTable().ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'Could not delete district.', 'error');
                    }
                });
            }
        });
    }

    
    </script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pincode</title>
</head>
<body>  

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif   
        
    <div>
        <label class="form-label mt-2" for="pincode">Pincode:</label>
        <input type="text" name="pincode" class="form-control" id="pincode" required>
        @error('pincode')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label mt-2" for="postname">Postname:</label>
        <input type="text" name="postname" class="form-control" id="postname" required>
        @error('postname')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label mt-2" for="state_id">State:</label>
        <select name="state_id" id="state_id" class="form-control">
            <option value="">-- Select State --</option>
            @foreach ($states as $state)
                <option value="{{ $state->id }}">{{ $state->state_name }}</option>
            @endforeach
        </select>
        @error('state_id')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label mt-2" for="district_id">District:</label>
        <select name="district_id" id="district_id" class="form-control" required>
            <option value="">-- Select District --</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" data-id="{{$district->state_id}}">{{ $district->district_name }}</option>
            @endforeach
        </select>
        @error('district_id')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn btn-primary btn-success float-right mt-2" onclick="savePincodeButton()">Submit</button>
    <script>
        $(document).ready(function () {
            // Event listener for #state_id selection change
            $('#state_id').on('change', function () {
                const state_id = $(this).val();
                $('#district_id option').each(function () {
                    const optionstate_id = $(this).data('id');
                    if (state_id=== "" || optionstate_id == state_id) {
                        $(this).show(); // Show matching options
                    } else {
                        $(this).hide(); // Hide non-matching options
                    }
                });
                // Reset district_id to the default option
                $('#district_id').val('');
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add District</title>
</head>
<body>
   

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

   
        
        <div>
            <label class="form-label mt-2" for="district_name">District Name:</label>
            <input type="text" name="district_name" class="form-control" id="district_name" required>
            @error('district_name')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="form-label mt-2" for="state_id">State:</label>
            <select name="state_id" id="state_id" class="form-control" required>
                <option value="">-- Select State --</option>
                @foreach ($states as $state)
                    <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                @endforeach
            </select>
            @error('first_category_id')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn btn-primary btn-success float-right mt-2" onclick="saveCategoryButton()">Submit</button>
   
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Third Category</title>
</head>
<body>  

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif   
        
    <div>
        <label class="form-label mt-2" for="third_category_name">Third Category Name:</label>
        <input type="text" name="third_category_name" class="form-control" id="third_category_name" required>
        @error('third_category_name')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label mt-2" for="first_category_id">First Category:</label>
        <select name="first_category_id" id="first_category_id" class="form-control">
            <option value="">-- Select First Category --</option>
            @foreach ($firstCategories as $category)
                <option value="{{ $category->id }}">{{ $category->first_category_name }}</option>
            @endforeach
        </select>
        @error('first_category_id')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label mt-2" for="second_category_id">Second Category:</label>
        <select name="second_category_id" id="second_category_id" class="form-control" required>
            <option value="">-- Select Second Category --</option>
            @foreach ($secondCategories as $category)
                <option value="{{ $category->id }}" data-id="{{$category->first_category_id}}">{{ $category->second_category_name }}</option>
            @endforeach
        </select>
        @error('second_category_id')
            <p style="color: red;">{{ $message }}</p>
        @enderror
    </div>

    <button class="btn btn-primary btn-success float-right mt-2" onclick="saveCategoryButton()">Submit</button>
    <script>
        $(document).ready(function () {
            // Event listener for #first_category_id selection change
            $('#first_category_id').on('change', function () {
                const selectedFirstCategoryId = $(this).val();
                $('#second_category_id option').each(function () {
                    const optionFirstCategoryId = $(this).data('id');
                    if (selectedFirstCategoryId === "" || optionFirstCategoryId == selectedFirstCategoryId) {
                        $(this).show(); // Show matching options
                    } else {
                        $(this).hide(); // Hide non-matching options
                    }
                });
                // Reset second_category_id to the default option
                $('#second_category_id').val('');
            });
        });
    </script>
</body>
</html>

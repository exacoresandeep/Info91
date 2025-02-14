<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Second Category</title>
</head>
<body>
   

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

   
        
        <div>
            <label class="form-label mt-2" for="second_category_name">Second Category Name:</label>
            <input type="text" name="second_category_name" class="form-control" id="second_category_name" required>
            @error('second_category_name')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="form-label mt-2" for="first_category_id">First Category:</label>
            <select name="first_category_id" id="first_category_id" class="form-control" required>
                <option value="">-- Select First Category --</option>
                @foreach ($firstCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->first_category_name }}</option>
                @endforeach
            </select>
            @error('first_category_id')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn btn-primary btn-success float-right mt-2" onclick="saveCategoryButton()">Submit</button>
   
</body>
</html>

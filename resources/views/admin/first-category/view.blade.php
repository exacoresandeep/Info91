<table class="table borderless">
    <tr>
        <th>Name</th>
        <td>: {{ $category->first_category_name }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $category->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $category->created_at }}</td>
    </tr>
</table>

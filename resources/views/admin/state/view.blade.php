<table class="table borderless">
    <tr>
        <th>Name</th>
        <td>: {{ $state->state_name }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $state->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $state->created_at }}</td>
    </tr>
</table>

<table class="table borderless">
    <tr>
        <th>Name</th>
        <td>: {{ $district->district_name }}</td>
    </tr>
    <tr>
        <th>State</th>
        <td>: {{ $district->state->state_name }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $district->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $district->created_at ?? 'NA' }}</td>
    </tr>
</table>

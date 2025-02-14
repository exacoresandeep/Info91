<table class="table borderless">
    <tr>
        <th>Pincode</th>
        <td>: {{ $pincode->pincode }}</td>
    </tr>
    <tr>
        <th>Postname</th>
        <td>: {{ $pincode->postname }}</td>
    </tr>
    <tr>
        <th>District</th>
        <td>: {{ $pincode->district->district_name }}</td>
    </tr>
    <tr>
        <th>State</th>
        <td>: {{ $pincode->district->state->state_name }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $pincode->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $pincode->created_at ?? 'NA' }}</td>
    </tr>
</table>

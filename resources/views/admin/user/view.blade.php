<table class="table borderless">
    <tr>
        <th>User ID</th>
        <td>: {{ $user->id }}</td>
    </tr>
    <tr>
        <th>Name</th>
        <td>: {{ $user->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Phone Number</th>
        <td>: {{ $user->phone_number ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>District Name</th>
        <td>: {{ $user->pincodeDetails->district->district_name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>State Name</th>
        <td>: {{ $user->pincodeDetails->district->state->state_name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Pincode</th>
        <td>: {{ $user->pincode ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $user->created_at->format('Y-m-d H:i:s') }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $user->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
</table>

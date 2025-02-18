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
        <td>
            <input type="hidden" id="user_id" value="{{ $user->id }}">
            <select class="form-control" id="edit_status">
                <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>Inactive</option>
                <option value="2" {{ $user->status == 2 ? 'selected' : '' }}>Blocked</option>
            </select>
        </td>
    </tr>
</table>

<button type="button" class="btn btn-primary float-right" id="saveStatusBtn">Save Changes</button>

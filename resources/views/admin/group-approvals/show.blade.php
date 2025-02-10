<div class="view-page">
    <table class="table borderless">
        <tbody>
            <tr>
                <td><strong>Group Name</strong></td>
                <td>: <strong>{{ $group->group_name }}</strong></td>
            </tr>
            <tr>
                <td>Type</td>
                <td>: {{ $group->type }}</td>
            </tr>
            <tr>
                <td>Purpose</td>
                <td>: {{ $group->purpose }}</td>
            </tr>
            <tr>
                <td>Category 1</td>
                <td>: {{ $group->firstCategory->first_category_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Category 2</td>
                <td>: {{ $group->secondCategory->second_category_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Category 3</td>
                <td>: {{ $group->thirdCategory->third_category_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Plan</td>
                <td>: {{ $group->plan->plan_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Mobile Number</td>
                <td>: {{ $group->mobile_number }}</td>
            </tr>
            <tr>
                <td>Created Date</td>
                <td>: {{ $group->formatted_created_at }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: 
                    @if ($group->status == 0)
                        Not Approved
                    @elseif ($group->status == 1)
                        Approved
                    @else
                        Blocked
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

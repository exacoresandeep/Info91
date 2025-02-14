<table class="table borderless">
    <tr>
        <th>Name</th>
        <td>: {{ $plan->plan_name }}</td>
    </tr>
    <tr>
        <th>Amount</th>
        <td>: {{ $plan->amount }}</td>
    </tr>
    <tr>
        <th>Duration</th>
        <td>: {{ $plan->duration }}</td>
    </tr>
    <tr>
        <th>Tax</th>
        <td>: {{ $plan->tax }}</td>
    </tr>
    <tr>
        <th>Members Limit</th>
        <td>: {{ $plan->total_members }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>: {{ $plan->status == 1 ? 'Active' : 'Inactive' }}</td>
    </tr>
    <tr>
        <th>Created Date</th>
        <td>: {{ $plan->created_at??'NA' }}</td>
    </tr>
</table>

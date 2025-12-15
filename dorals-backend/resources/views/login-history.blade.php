
@section('content')
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-semibold mb-4">Login History</h2>

        <table class="min-w-full table-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2">Timestamp</th>
                    <th class="px-4 py-2">User ID</th>
                    <th class="px-4 py-2">User Type</th>
                    <th class="px-4 py-2">IP Address</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loginHistory as $history)
                    <tr>
                        <td class="px-4 py-2">{{ $history->login_time }}</td>
                        <td class="px-4 py-2">{{ $history->user_id }}</td>
                        <td class="px-4 py-2">{{ $history->user_type }}</td>
                        <td class="px-4 py-2">{{ $history->ip_address ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $history->action ?? 'Login' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        {{ $loginHistory->links() }}
    </div>
@endsection

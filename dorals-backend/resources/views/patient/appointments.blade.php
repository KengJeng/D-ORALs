<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Appointments - D-ORALS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: system-ui, Arial, sans-serif; }
        .card { background: #fff; border-radius:6px; padding:16px; box-shadow:0 1px 4px rgba(0,0,0,0.06); margin-bottom:12px }
        .list { list-style:none; padding:0; margin:0 }
        .list li { padding:12px 0; border-bottom:1px solid #eee }
        .muted { color:#666; font-size:0.95rem }
        .empty { color:#999; padding:12px 0 }
        .status-badge { display:inline-block; padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600 }
        .status-pending { background:#fef3c7; color:#92400e }
        .status-confirmed { background:#dbeafe; color:#1e40af }
        .status-completed { background:#dcfce7; color:#166534 }
        .status-canceled { background:#fee2e2; color:#991b1b }
        .status-noshow { background:#f3e8ff; color:#6b21a8 }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                </svg>
                <span class="text-2xl font-bold">D-ORALS</span>
            </div>
            <div class="flex items-center space-x-4">
                <span id="patientName" class="text-sm">Patient</span>
                <a href="/patient/dashboard" class="bg-blue-500 hover:bg-blue-400 px-4 py-2 rounded text-sm transition duration-200">
                    Book Appointment
                </a>
                <a href="/patient/dashboard/view" class="bg-blue-500 hover:bg-blue-400 px-4 py-2 rounded text-sm transition duration-200">
                    Dashboard
                </a>
                <form action="/patient/logout" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition duration-200">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">My Appointments</h1>
                <p class="text-gray-600">View all your scheduled and past appointments</p>
            </div>

            <!-- Appointments List -->
            <div class="bg-white rounded-lg shadow-md p-6">
                @if ($appointments->count())
                    <ul class="list">
                        @foreach ($appointments as $apt)
                            <li>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-gray-800">{{ $apt->scheduled_date }}</div>
                                        <div class="muted">{{ $apt->services->pluck('name')->join(', ') ?: 'Service' }}</div>
                                    </div>
                                    <span class="status-badge status-{{ strtolower(str_replace('-', '', $apt->status)) }}">
                                        {{ $apt->status }}
                                    </span>
                                </div>
                                <div class="status-noshow status-{{ strtolower(str_replace('-', '', $apt->queue_number)) }}">Queue #: {{ $apt->queue_number ?? 'N/A' }}</div>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center">
                        {{ $appointments->links() }}
                    </div>
                @else
                    <div class="empty text-center py-12">
                        <p class="text-gray-500">You have no appointments yet.</p>
                        <a href="/patient/dashboard" class="text-blue-600 hover:underline mt-2 inline-block">Book your first appointment</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Display patient name from session or localStorage
        const patient = {{ json_encode(session('patient')) }};
        if (patient && patient.first_name) {
            document.getElementById('patientName').textContent = patient.first_name + ' ' + (patient.last_name || '');
        }
    </script>
</body>
</html>

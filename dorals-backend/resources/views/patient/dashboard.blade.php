<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Dashboard</title>
    <style>
        body { font-family: system-ui, Arial, sans-serif; padding: 20px; background:#f7f7f8; }
        h1 { margin-bottom: 8px; }
        .card { background: #fff; border-radius:6px; padding:16px; box-shadow:0 1px 4px rgba(0,0,0,0.06); margin-bottom:12px }
        .list { list-style:none; padding:0; margin:0 }
        .list li { padding:8px 0; border-bottom:1px solid #eee }
        .muted { color:#666; font-size:0.95rem }
        .empty { color:#999; padding:12px 0 }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h1 style="margin:0">Patient Dashboard</h1>
        <div style="display:flex; gap:8px">
            <a href="/patient/appointments" style="background:#10b981; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:0.9rem;">
                My Appointments
            </a>
            <a href="/patient/dashboard" style="background:#0b5ed7; color:#fff; padding:8px 16px; border-radius:4px; text-decoration:none; font-size:0.9rem;">
                Book Appointment
            </a>
        </div>
    </div>

    <div class="card" id="summary">
        <strong>Summary</strong>
        <div class="muted">
            <div><strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong></div>
            <div class="muted">Upcoming appointments: {{ $upcoming_count ?? 0 }}</div>
            <div class="muted">Last appointment: {{ $last_appointment->scheduled_date ?? '—' }} {{ $last_appointment->status ?? '' }}</div>
        </div>
    </div>

    <div class="card">
        <strong>Upcoming Appointments</strong>
        <ul class="list">
            @if(isset($upcoming) && $upcoming->count())
                @foreach($upcoming as $a)
                    <li>
                        <div><strong>{{ $a->scheduled_date }}</strong> — {{ $a->services->pluck('name')->join(', ') ?: 'Service' }}</div>
                        <div class="muted">Queue: {{ $a->queue_number ?? '-' }} · Status: {{ $a->status ?? '-' }}</div>
                    </li>
                @endforeach
            @else
                <li class="empty">No upcoming appointments.</li>
            @endif
        </ul>
    </div>

    <div class="card">
        <strong>Services</strong>
        <ul class="list">
            @if(isset($services) && $services->count())
                @foreach($services as $s)
                    <li>
                        <div><strong>{{ $s->name }}</strong> <span class="muted">({{ $s->duration ?? '-' }} mins)</span></div>
                    </li>
                @endforeach
            @else
                <li class="empty">No services available.</li>
            @endif
        </ul>
    </div>
</body>
</html>

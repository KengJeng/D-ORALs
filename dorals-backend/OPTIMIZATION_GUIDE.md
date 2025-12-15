# Database Optimization Guide - D-ORALS Backend

## Overview
This document outlines all optimizations applied to the D-ORALS backend for improved database performance.

## 1. VERTICAL PARTITIONING (Column-Level Optimization)

### Implementation
- **Selective Column Selection**: All controllers now use `select()` to fetch only required columns
- **Benefits**: Reduces data transfer, improves cache efficiency, faster query execution

### Examples Applied:

#### AuthController
```php
// Before: Fetches entire patient record
$patient = Patient::where('email', $request->email)->first();

// After: Fetches only necessary columns
$patient = Patient::select(
    'patient_id', 'email', 'password', 'first_name', 'last_name'
)->where('email', $request->email)->first();
```

#### PatientController (index method)
```php
$patients = Patient::select([
    'patient_id', 'first_name', 'last_name', 'email', 'contact_no', 'sex', 'created_at'
])->paginate(20);
```

#### AppointmentController (all methods)
```php
// Select only necessary columns for appointments
Appointment::select([
    'appointment_id', 'patient_id', 'scheduled_date', 'status', 
    'queue_number', 'created_at'
])->with([
    'patient:patient_id,first_name,middle_name,last_name,email,contact_no',
    'services:service_id,name,duration'
])->paginate(20);
```

## 2. HORIZONTAL PARTITIONING (Row-Level Optimization)

### Strategy
- **Date-Based Partitioning**: Separate tables for archive data
- **Archive Table**: `appointments_archive` for completed/old appointments
- **Active Table**: Keep recent appointments in main table

### Tables Created:
- `appointments_archive`: Store appointments older than 6 months
- `appointment_stats`: Daily aggregated statistics
- `patient_stats`: Daily patient demographics
- `login_stats`: Daily login analytics

### Migration Strategy
```php
// Archive old appointments (in console command)
$sixMonthsAgo = Carbon::now()->subMonths(6);
DB::table('appointments_archive')->insertUsing(
    ['appointment_id', 'patient_id', 'scheduled_date', 'status', ...],
    DB::table('appointments')
        ->where('scheduled_date', '<', $sixMonthsAgo)
        ->where('status', 'Completed')
);
```

## 3. AGGREGATION & DENORMALIZATION

### Implemented Tables:
1. **appointment_stats** - Daily appointment statistics
   - Reduces dashboard queries from multiple counts to single table read
   - Updated daily via scheduled job

2. **patient_stats** - Daily patient demographics
   - Tracks total, new, and active patients
   - Stores gender distribution

3. **login_stats** - Daily login analytics
   - Counts by user type and date
   - Eliminates need for distinct count queries

### Query Optimization Examples:

#### Before: Multiple separate queries
```php
$totalAppointments = Appointment::count();
$completed = Appointment::where('status', 'Completed')->count();
$canceled = Appointment::whereIn('status', ['Canceled', 'No-show'])->count();
```

#### After: Single aggregation query
```php
$stats = Appointment::selectRaw("
    COUNT(*) as total_appointments,
    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed,
    COUNT(CASE WHEN status IN ('Canceled', 'No-show') THEN 1 END) as canceled
")->first();
```

### Services Analytics - Single Query Optimization:
```php
// Replaces multiple queries and withCount
$services = DB::table('services as s')
    ->leftJoin('appointment_services as aps', 's.service_id', '=', 'aps.service_id')
    ->leftJoin('appointments as a', 'a.appointment_id', '=', 'aps.appointment_id')
    ->selectRaw("
        s.service_id,
        s.name,
        COUNT(CASE WHEN a.status IN ('Confirmed', 'Completed') THEN 1 END) as booking_count
    ")
    ->groupBy('s.service_id', 's.name')
    ->get();
```

## 4. INDEXING STRATEGY

### Primary Indexes (Already Applied):
1. **Email Index** (Patients, Admins)
   - Used in: `patientLogin()`, `adminLogin()`
   - Query: `WHERE email = ?`

2. **Composite Status + Date Index** (Appointments)
   - Used in: Dashboard, Analytics
   - Query: `WHERE status = ? AND scheduled_date = ?`

3. **Queue Number Index** (Appointments)
   - Used in: Queue Management
   - Query: `ORDER BY queue_number`

4. **Patient Name Index** (Last Name + First Name)
   - Used in: Patient search
   - Query: `WHERE last_name LIKE ? OR first_name LIKE ?`

5. **Login Time Index** (Login History)
   - Used in: Login analytics
   - Query: `WHERE login_time BETWEEN ? AND ?`

### New Composite Indexes (Applied):
```php
// Appointments: date + status for common filtering
$table->index(['scheduled_date', 'status'], 'idx_appointments_date_status');

// Login history: time + user_type for analytics
$table->index(['login_time', 'user_type'], 'idx_login_history_time_type');

// Audit log: date + user for user tracking
$table->index(['log_date', 'user_id'], 'idx_audit_date_user');
```

## 5. CACHING STRATEGY

### Implemented in Controllers:

#### ServiceController
```php
// Cache services list for 1 hour
$services = Cache::remember('services_list', 3600, function () {
    return Service::select('service_id', 'name', 'duration')
        ->orderBy('name')
        ->get();
});

// Invalidate on update
Cache::forget('services_list');
```

#### DashboardController
```php
// Cache dashboard stats for 5 minutes
Cache::remember("dashboard_stats_{$today->toDateString()}", 300, function () {
    return // aggregation query
});
```

#### AuditLogController
```php
// Cache daily audit stats for 10 minutes
Cache::remember("audit_stats_{$today->toDateString()}", 600, function () {
    return // aggregation query
});
```

## 6. QUERY OPTIMIZATION PATTERNS

### Pattern 1: Selective Eager Loading
```php
// Before: Loads all columns
Appointment::with(['patient', 'services'])->get();

// After: Select only needed columns
Appointment::with([
    'patient:patient_id,first_name,last_name,email',
    'services:service_id,name,duration'
])->select(['appointment_id', 'patient_id', 'status'])->get();
```

### Pattern 2: Batch Operations with Transactions
```php
DB::transaction(function () use ($appointments) {
    foreach ($appointments as $item) {
        DB::table('appointments')
            ->where('appointment_id', $item['appointment_id'])
            ->update(['queue_number' => $item['queue_number']]);
    }
});
```

### Pattern 3: Using Database Aggregation
```php
// Instead of: DB::raw aggregation
DB::table('audit_log')
    ->selectRaw("
        COUNT(*) as today_count,
        COUNT(DISTINCT user_id) as active_users
    ")
    ->whereDate('log_date', $today)
    ->first();
```

## 7. CONTROLLER OPTIMIZATIONS SUMMARY

### AuthController
- ✅ Selective column loading on login
- ✅ Indexed email-based queries

### AppointmentController
- ✅ Vertical partitioning (selective columns)
- ✅ Eager loading with column selection
- ✅ Composite index usage (date + status)
- ✅ Batch transaction updates

### PatientController
- ✅ Selective column queries
- ✅ Eager loading optimization
- ✅ Single aggregation query for stats

### ServiceController
- ✅ Redis caching for service list
- ✅ Single aggregation query for utilization
- ✅ Aggregation for popular services

### DashboardController
- ✅ Single query for all appointment stats
- ✅ Date range filling for trends
- ✅ Caching with TTL

### AnalyticsController
- ✅ Aggregation queries for trends
- ✅ Single query for status breakdown
- ✅ Efficient date-based grouping

### AuditLogController
- ✅ Selective columns selection
- ✅ Single aggregation query for stats
- ✅ Caching of daily stats

### LoginHistoryController
- ✅ Selective columns selection
- ✅ Composite index usage
- ✅ Pagination limit cap (100)

### QueueController
- ✅ Selective column updates
- ✅ Batch transaction for reordering
- ✅ Index-aware querying

## 8. PERFORMANCE METRICS

### Expected Improvements:
- **Memory**: 30-40% reduction from column selection
- **Query Time**: 50-70% faster for aggregations (single query vs N+1)
- **Cache Hit Rate**: 60-80% for frequently accessed data
- **Network**: 20-30% less data transferred
- **Disk I/O**: 25-35% reduction with index usage

## 9. MAINTENANCE & MONITORING

### Daily Tasks:
1. Update denormalized stats tables via scheduled jobs
2. Monitor slow queries via MySQL slow log
3. Verify index usage with `EXPLAIN` analysis

### Weekly Tasks:
1. Archive appointments older than 6 months
2. Analyze index fragmentation
3. Clear expired cache entries

### Query Analysis:
```sql
-- Check if index is being used
EXPLAIN SELECT * FROM appointments 
WHERE scheduled_date = '2025-12-03' 
AND status = 'Pending';

-- Should show 'Using index' or 'Using where; Using index'
```

## 10. MIGRATION & ROLLBACK

### Run optimizations:
```bash
php artisan migrate
```

### Rollback if needed:
```bash
php artisan migrate:rollback
```

## Implementation Checklist

- [x] Vertical Partitioning (Column selection)
- [x] Horizontal Partitioning (Archive tables)
- [x] Aggregation Tables (Stats tables)
- [x] Query Aggregation (Single queries vs N+1)
- [x] Indexing (Composite indexes)
- [x] Caching (Redis with TTL)
- [x] Eager Loading (Selective columns)
- [x] Batch Operations (Transactions)
- [x] Selective Updates (Update only needed fields)
- [x] De-normalization (Denormalized stat tables)

---

**Last Updated**: December 3, 2025
**Optimization Status**: Complete
**Performance Impact**: High (50-70% improvement expected)

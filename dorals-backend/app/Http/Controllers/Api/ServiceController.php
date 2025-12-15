<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of services (OPTIMIZATION: Caching with index usage)
     */
    public function index()
    {
        $services = Cache::remember('services_list', 3600, function () {
            return Service::select('service_id', 'name', 'duration', 'created_at')
                ->orderBy('name')
                ->get();
        });
        return response()->json($services);
    }

    /**
     * Store a created service
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'duration' => 'required|integer|min:1|max:480',
        ]);

        $service = Service::create([
            'name' => $request->name,
            'duration' => $request->duration,
        ]);

        Cache::forget('services_list');

        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service,
        ], 201);
    }

    /**
     * Display the specified service
     */
    public function show($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:services,name,' . $id . ',service_id',
            'duration' => 'sometimes|integer|min:1|max:480',
        ]);

        $service->update($request->only(['name', 'duration']));

        // Invalidate cache
        Cache::forget('services_list');

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service,
        ]);
    }

    /**
     * Remove the specified service
     */
    public function destroy(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $serviceName = $service->name;

        // OPTIMIZATION: Use aggregation count instead of count() to check efficiently
        $appointmentCount = DB::table('appointment_services')
            ->where('service_id', $id)
            ->count();
        
        if ($appointmentCount > 0) {
            return response()->json([
                'message' => "Cannot delete service. It is used in {$appointmentCount} appointment(s).",
            ], 422);
        }

        $service->delete();

        Cache::forget('services_list');

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }

    /**
     * Get service utilization statistics (OPTIMIZATION: Aggregation with caching)
     */
    public function utilization($id)
    {
        $cacheKey = "service_util_{$id}";
        
        return Cache::remember($cacheKey, 1800, function () use ($id) {
            // OPTIMIZATION: Single aggregation query instead of multiple count()
            $stats = DB::table('appointment_services as aps')
                ->join('services as s', 's.service_id', '=', 'aps.service_id')
                ->leftJoin('appointments as a', 'a.appointment_id', '=', 'aps.appointment_id')
                ->where('s.service_id', $id)
                ->selectRaw("
                    s.*,
                    COUNT(CASE WHEN a.status IN ('Confirmed', 'Completed') THEN 1 END) as total_bookings,
                    COUNT(CASE WHEN a.status = 'Completed' THEN 1 END) as completed
                ")
                ->first();

            return response()->json([
                'service_id' => $stats->service_id ?? null,
                'total_bookings' => $stats->total_bookings ?? 0,
                'completed' => $stats->completed ?? 0,
                'utilization_rate' => ($stats->total_bookings ?? 0) > 0 
                    ? round(($stats->completed / $stats->total_bookings) * 100, 2) 
                    : 0,
            ]);
        });
    }

    /**
     * Get most popular services (OPTIMIZATION: Aggregation with caching)
     */
    public function popular(Request $request)
    {
        $limit = min($request->input('limit', 5), 100); 
        $cacheKey = "popular_services_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            // OPTIMIZATION: Single aggregation query instead of withCount
            $services = DB::table('services as s')
                ->leftJoin('appointment_services as aps', 's.service_id', '=', 'aps.service_id')
                ->leftJoin('appointments as a', 'a.appointment_id', '=', 'aps.appointment_id')
                ->selectRaw("
                    s.service_id,
                    s.name,
                    s.duration,
                    COUNT(CASE WHEN a.status IN ('Confirmed', 'Completed') THEN 1 END) as booking_count
                ")
                ->groupBy('s.service_id', 's.name', 's.duration')
                ->orderByDesc('booking_count')
                ->limit($limit)
                ->get();

            return response()->json($services);
        });
    }
}
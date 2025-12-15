<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     * Option A: email is in users table, so we join users.
     */
    public function index(Request $request)
    {
        $query = Patient::query()
            ->leftJoin('users', 'patients.user_id', '=', 'users.id');

        // Select only necessary columns for listing (email from users)
        $query->select([
            'patients.patient_id',
            'patients.user_id',
            'patients.first_name',
            'patients.middle_name',
            'patients.last_name',
            'users.email as email',
            'patients.contact_no',
            'patients.sex',
            'patients.address',
            'patients.created_at',
            'patients.deleted_at',
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('patients.first_name', 'like', "%{$search}%")
                    ->orWhere('patients.last_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('patients.contact_no', 'like', "%{$search}%");
            });
        }

        // Filter by sex
        if ($request->filled('sex')) {
            $query->where('patients.sex', $request->input('sex'));
        }

        $patients = $query->orderBy('patients.created_at', 'desc')->paginate(20);

        return response()->json($patients);
    }

    /**
     * Display the specified patient
     * Option A: include email from users
     */
    public function show($id)
    {
        $patient = Patient::with(['appointments' => function ($query) {
            $query->select([
                'appointment_id',
                'patient_id',
                'scheduled_date',
                'status',
                'queue_number',
                'created_at',
            ])->orderBy('scheduled_date', 'desc');
        }])->findOrFail($id);

        $email = optional($patient->user)->email;

        return response()->json([
            'patient' => $patient,
            'email'   => $email,
        ]);
    }

    /**
     * Update the specified patient (admin-side edit)
     * Option A: profile fields in patients; email in users.
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::with('user')->findOrFail($id);

        $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'sex'         => 'sometimes|in:Male,Female',
            'contact_no'  => 'sometimes|string|max:20',
            'address'     => 'sometimes|string',
            // Email uniqueness is now in users table
            'email'       => 'sometimes|email|max:255|unique:users,email,' . $patient->user_id,
        ]);

        DB::transaction(function () use ($request, $patient) {
            $patient->update($request->only([
                'first_name',
                'middle_name',
                'last_name',
                'sex',
                'contact_no',
                'address',
            ]));

            if ($request->filled('email') && $patient->user) {
                $patient->user->update([
                    'email' => $request->input('email'),
                ]);
            }
        });

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $patient->fresh(['user']),
        ]);
    }

    /**
     * Get patient's appointment history
     * FIXED to match your ERD:
     * appointments -> appointment_services -> services (services.service_id)
     */
    public function appointmentHistory($id)
    {
        Patient::select('patient_id')->findOrFail($id);

        $appointments = DB::table('appointments')
            ->where('appointments.patient_id', $id)
            ->leftJoin('appointment_services', 'appointments.appointment_id', '=', 'appointment_services.appointment_id')
            ->leftJoin('services', 'appointment_services.service_id', '=', 'services.service_id')
            ->select([
                'appointments.appointment_id',
                'appointments.scheduled_date',
                'appointments.status',
                'appointments.queue_number',
                'appointments.created_at',
                'services.service_id as service_id',
                'services.name as service_name',
                'services.duration as service_duration',
            ])
            ->orderBy('appointments.scheduled_date', 'desc')
            ->paginate(20);

        return response()->json($appointments);
    }

    /**
     * Get patient statistics
     * Option A: patient table no longer stores email; return user email separately.
     */
    public function statistics($id)
    {
        $patient = Patient::with('user:id,email')->select([
            'patient_id',
            'user_id',
            'first_name',
            'middle_name',
            'last_name',
            'sex',
        ])->findOrFail($id);

        $stats = DB::table('appointments')
            ->where('patient_id', $id)
            ->selectRaw("
                COUNT(*) as total_appointments,
                COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'Canceled' THEN 1 END) as canceled,
                COUNT(CASE WHEN status = 'No-show' THEN 1 END) as no_show,
                COUNT(CASE
                    WHEN scheduled_date >= CURDATE()
                    AND status IN ('Pending', 'Confirmed')
                    THEN 1
                END) as upcoming
            ")
            ->first();

        $completionRate = $stats->total_appointments > 0
            ? round(($stats->completed / $stats->total_appointments) * 100, 2)
            : 0;

        return response()->json([
            'patient' => [
                'patient_id' => $patient->patient_id,
                'first_name' => $patient->first_name,
                'middle_name' => $patient->middle_name,
                'last_name' => $patient->last_name,
                'sex' => $patient->sex,
                'email' => optional($patient->user)->email,
            ],
            'statistics' => [
                'total_appointments' => (int) $stats->total_appointments,
                'completed' => (int) $stats->completed,
                'canceled' => (int) $stats->canceled,
                'no_show' => (int) $stats->no_show,
                'upcoming' => (int) $stats->upcoming,
                'completion_rate' => $completionRate,
            ],
        ]);
    }

    /**
     * Delete patient account (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        $patient = Patient::select('patient_id', 'first_name', 'middle_name', 'last_name')
            ->findOrFail($id);

        $upcomingCount = DB::table('appointments')
            ->where('patient_id', $id)
            ->where('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->count();

        if ($upcomingCount > 0) {
            return response()->json([
                'message' => "Cannot delete patient. There are {$upcomingCount} upcoming appointment(s).",
            ], 422);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient account marked as deleted successfully',
        ]);
    }

    /**
     * Get current authenticated patient profile
     * auth:sanctum -> request->user() is User
     */
    public function profile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $patient = Patient::where('user_id', $user->id)->first();

        if (! $patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'patient' => $patient,
        ]);
    }

    /**
     * Update current authenticated patient profile
     * patients table for profile fields; users table for email
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $patient = Patient::where('user_id', $user->id)->first();

        if (! $patient) {
            return response()->json(['message' => 'Patient profile not found'], 404);
        }

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'sex'         => 'sometimes|in:Male,Female',
            'contact_no'  => 'required|string|max:20',
            'address'     => 'nullable|string',
            'email'       => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        DB::transaction(function () use ($request, $user, $patient) {
            $patient->update([
                'first_name'  => $request->input('first_name'),
                'middle_name' => $request->input('middle_name'),
                'last_name'   => $request->input('last_name'),
                'sex'         => $request->input('sex'),
                'contact_no'  => $request->input('contact_no'),
                'address'     => $request->input('address'),
            ]);

            $user->update([
                'email' => $request->input('email'),
            ]);
        });

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
            'patient' => $patient->fresh(),
        ]);
    }

    /**
     * Change password (users table)
     */
    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 400);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function patientRegister(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'sex'         => 'required|in:Male,Female',
            'contact_no'  => 'required|string|max:20',
            'address'     => 'required|string',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $data = DB::transaction(function () use ($request) {

            $user = User::create([
                'name'     => trim($request->first_name.' '.$request->last_name),
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'patient',
            ]);

            $patient = Patient::create([
                'user_id'     => $user->id,
                'first_name'  => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name'   => $request->last_name,
                'sex'         => $request->sex,
                'contact_no'  => $request->contact_no,
                'address'     => $request->address,
            ]);

            $token = $user->createToken('patient-token')->plainTextToken;

            DB::table('login_history')->insert([
                'user_id'    => $user->id,
                'user_type'  => 'patient',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => DB::raw('CURRENT_TIMESTAMP'),
            ]);

            return compact('user', 'patient', 'token');
        });

        return response()->json([
            'message' => 'Registration successful',
            'user'    => ['id' => $data['user']->id, 'email' => $data['user']->email],
            'patient' => $data['patient'],
            'token'   => $data['token'],
        ], 201);
    }

    public function patientLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'patient')
            ->first();


        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $patient = Patient::where('user_id', $user->id)->first();

        if (! $patient) {
            throw ValidationException::withMessages([
                'email' => ['This account is not registered as a patient.'],
            ]);
        }

        $token = $user->createToken('patient-token')->plainTextToken;

        DB::table('login_history')->insert([
            'user_id'    => $user->id,
            'user_type'  => 'patient',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_time' => DB::raw('CURRENT_TIMESTAMP'),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user'    => ['id' => $user->id, 'email' => $user->email],
            'patient' => $patient,
            'token'   => $token,
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                ->where('role', 'admin')
                ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $admin = Admin::where('user_id', $user->id)->first();

        if (! $admin) {
            throw ValidationException::withMessages([
                'email' => ['This account is not registered as an admin.'],
            ]);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        DB::table('login_history')->insert([
            'user_id'    => $user->id,
            'user_type'  => 'admin',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_time' => DB::raw('CURRENT_TIMESTAMP'),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user'    => ['id' => $user->id, 'email' => $user->email],
            'admin'   => $admin,
            'token'   => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}

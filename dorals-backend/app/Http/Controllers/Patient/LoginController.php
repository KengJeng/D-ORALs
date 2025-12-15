<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle API-based patient login (returns API token)
     */
    public function login(Request $request)
    {
        // Validate the input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find the patient by email
        $patient = Patient::where('email', $request->email)->first();

        // Check if the patient exists and password is correct
        if (!$patient || !Hash::check($request->password, $patient->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate an API token for the patient using Sanctum
        $token = $patient->createToken('patient-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'patient' => $patient,
            'token' => $token,
        ]);
    }

    /**
     * Handle API-based patient logout (revokes token)
     */
    public function logout(Request $request)
    {
        // Revoke the current API token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}

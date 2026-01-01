<?php
// app/Http/Controllers/Api/AuthController.php - FINAL VERSION

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * ✅ Verify token and get COMPLETE user details
     */
    public function verifyToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::with('patient')
            ->where('email', $request->email)
            ->where('account_completion_token', $request->token)
            ->where('registered_by_staff', true)
            ->where('account_completed', false)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired registration link'
            ], 404);
        }

        // Check if token is expired (7 days)
        if ($user->created_at->addDays(7)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration link has expired. Please contact the clinic.'
            ], 410);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token verified successfully',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'address' => $user->address,
                'patient_id' => $user->patient->patient_id ?? null,
                'phone_number' => $user->patient->phone_number ?? null,
                'date_of_birth' => $user->patient->date_of_birth ?? null,
                'gender' => $user->patient->gender ?? null,
                'emergency_contact' => $user->patient->emergency_contact ?? null,
            ]
        ], 200);
    }

    /**
     * ✅ Register or Complete Registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'phone_number' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // CHECK 1: Token-based registration (staff registered)
            if ($request->has('token') && !empty($request->token)) {
                $existingUser = User::where('email', $request->email)
                    ->where('account_completion_token', $request->token)
                    ->where('registered_by_staff', true)
                    ->where('account_completed', false)
                    ->first();

                if (!$existingUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid registration link'
                    ], 404);
                }

                // Complete the existing account
                $existingUser->update([
                    'password' => Hash::make($request->password),
                    'account_completed' => true,
                    'account_completion_token' => null,
                ]);

                // Update patient info
                if ($existingUser->patient) {
                    $existingUser->patient->update([
                        'phone_number' => $request->phone_number,
                        'date_of_birth' => $request->date_of_birth,
                        'gender' => $request->gender,
                        'emergency_contact' => $request->emergency_contact,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => '✅ Registration completed! You can now login.',
                    'account_completed' => true,
                    'user' => [
                        'id' => $existingUser->id,
                        'name' => $existingUser->name,
                        'email' => $existingUser->email,
                        'role' => $existingUser->role,
                    ]
                ], 200);
            }

            // CHECK 2: Account already exists and completed
            if (User::where('email', $request->email)
                ->where('account_completed', true)
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered. Please login instead.'
                ], 422);
            }

            // CHECK 3: New normal registration
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'patient',
                'account_completed' => true,
                'registered_by_staff' => false,
            ]);

            Patient::create([
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'emergency_contact' => $request->emergency_contact,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please login to continue.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if account needs completion
            if ($user->registered_by_staff && !$user->account_completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete your registration first. Check your email for the registration link.',
                    'account_incomplete' => true
                ], 403);
            }

            if ($user->role !== 'patient') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile app access is only available for patients.'
                ], 403);
            }

            $user->load('patient');
            $user->update(['last_seen_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'patient_id' => $user->patient ? $user->patient->patient_id : null,
                    'phone_number' => $user->patient ? $user->patient->phone_number : null,
                    'gender' => $user->patient ? $user->patient->gender : null,
                    'date_of_birth' => $user->patient ? $user->patient->date_of_birth : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            $user = User::with('patient')->find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'patient' => $user->patient ? [
                        'patient_id' => $user->patient->patient_id,
                        'phone_number' => $user->patient->phone_number,
                        'gender' => $user->patient->gender,
                        'date_of_birth' => $user->patient->date_of_birth,
                        'age' => $user->patient->age,
                        'emergency_contact' => $user->patient->emergency_contact,
                        'blood_type' => $user->patient->blood_type,
                    ] : null
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::with('patient')->find($request->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update user name if provided
            if ($request->has('name')) {
                $user->update(['name' => $request->name]);
            }

            // Update patient data if provided
            if ($user->patient) {
                $patientData = [];

                if ($request->has('phone_number')) {
                    $patientData['phone_number'] = $request->phone_number;
                }

                if ($request->has('emergency_contact')) {
                    $patientData['emergency_contact'] = $request->emergency_contact;
                }

                if (!empty($patientData)) {
                    $user->patient->update($patientData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout (for cleanup purposes)
     */
    public function logout(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update(['last_seen_at' => now()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
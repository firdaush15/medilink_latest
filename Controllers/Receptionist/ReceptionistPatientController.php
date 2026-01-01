<?php
// app/Http/Controllers/Receptionist/ReceptionistPatientController.php - FINAL VERSION

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Helpers\PhoneHelper; // ✅ Import helper
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReceptionistPatientController extends Controller
{
    public function register()
    {
        $recentPatients = Patient::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('receptionist.receptionist_patientRegistration', compact('recentPatients'));
    }

    /**
     * ✅ Store new patient with phone number standardization
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female,Other',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'data_consent' => 'required|accepted',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Standardize phone numbers before saving
            $standardizedPhone = PhoneHelper::standardize($validated['phone_number']);
            $standardizedEmergency = PhoneHelper::standardize($validated['emergency_contact']);

            // ✅ Validate phone numbers
            if (!PhoneHelper::isValid($standardizedPhone)) {
                return back()
                    ->withInput()
                    ->withErrors(['phone_number' => 'Invalid phone number format. Please use format: 012-345 6789']);
            }

            if ($standardizedEmergency && !PhoneHelper::isValid($standardizedEmergency)) {
                return back()
                    ->withInput()
                    ->withErrors(['emergency_contact' => 'Invalid emergency contact format. Please use format: 012-345 6789']);
            }

            // Generate completion token
            $completionToken = Str::random(64);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(32)),
                'role' => 'patient',
                'address' => $validated['address'] ?? null,
                'account_completed' => false,
                'registered_by_staff' => true,
                'account_completion_token' => $completionToken,
            ]);

            // ✅ Create patient with standardized phone numbers
            $patient = Patient::create([
                'user_id' => $user->id,
                'phone_number' => $standardizedPhone, // ✅ Standardized format
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'emergency_contact' => $standardizedEmergency, // ✅ Standardized format
            ]);

            // Send completion email
            $this->sendAccountCompletionEmail($user, $completionToken);

            DB::commit();

            return redirect()
                ->route('receptionist.patients.register')
                ->with('success', "✅ Patient registered successfully! An email has been sent to {$user->email} with account setup instructions.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    protected function sendAccountCompletionEmail($user, $token)
    {
        $appName = config('app.name', 'MediLink');
        $completionLink = "medilink://complete-registration?token={$token}&email={$user->email}";

        Mail::send('emails.complete-registration', [
            'user' => $user,
            'token' => $token,
            'link' => $completionLink,
            'appName' => $appName,
        ], function ($message) use ($user, $appName) {
            $message->to($user->email)
                ->subject("Complete Your {$appName} Registration");
        });
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $patients = Patient::with('user')
            ->whereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orWhere('phone_number', 'like', "%{$query}%")
            ->orWhere('patient_id', $query)
            ->limit(10)
            ->get();

        return response()->json($patients);
    }
}
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
        // 1. Validate Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_number' => 'required|string|unique:patients,ic_number|regex:/^\d{6}-\d{2}-\d{4}$/', // Malaysian IC Format
            'email' => 'nullable|email|unique:users,email', // ✅ EMAIL IS NOW OPTIONAL
            'phone_number' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'data_consent' => 'required|accepted',
            // gender and dob are auto-derived from IC, but we accept overrides
            'gender' => 'required|in:Male,Female,Other', 
            'date_of_birth' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // 2. Handle Phone Standardization
            $standardizedPhone = PhoneHelper::standardize($validated['phone_number']);
            $standardizedEmergency = $validated['emergency_contact'] 
                ? PhoneHelper::standardize($validated['emergency_contact']) 
                : null;

            // 3. Handle Email Logic (The Real-World Solution)
            $hasEmail = !empty($validated['email']);
            
            if ($hasEmail) {
                // Scenario A: Patient has email
                $email = $validated['email'];
                $accountCompleted = false; // Needs setup
                $token = Str::random(64);
            } else {
                // Scenario B: No email (Elderly/Walk-in)
                // Generate fake internal email: 990101121234@medilink.local
                $cleanIC = str_replace(['-', ' '], '', $validated['ic_number']);
                $email = $cleanIC . '@medilink.local';
                $accountCompleted = true; // No setup needed, they are "offline" users
                $token = null;
            }

            // 4. Create User Account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make(Str::random(32)), // Random secure password
                'role' => 'patient',
                'address' => $validated['address'] ?? null,
                'account_completed' => $accountCompleted, 
                'registered_by_staff' => true,
                'account_completion_token' => $token,
            ]);

            // 5. Create Patient Record
            Patient::create([
                'user_id' => $user->id,
                'ic_number' => $validated['ic_number'], // ✅ Saved IC
                'phone_number' => $standardizedPhone,
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'emergency_contact' => $standardizedEmergency,
            ]);

            // 6. Send Email ONLY if provided
            if ($hasEmail && $token) {
                $this->sendAccountCompletionEmail($user, $token);
                $message = "✅ Patient registered! Setup email sent to {$user->email}.";
            } else {
                $message = "✅ Patient registered successfully (Offline Mode). No email sent.";
            }

            DB::commit();

            return redirect()
                ->route('receptionist.patients.register')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
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
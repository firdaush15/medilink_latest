<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // ✅ Redirect based on user role
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'doctor' => redirect()->route('doctor.dashboard'),
            'nurse' => redirect()->route('nurse.dashboard'),
            'receptionist' => redirect()->route('receptionist.dashboard'),
            'pharmacist' => redirect()->route('pharmacist.dashboard'),
            'patient' => redirect()->route('login')->with('info', 'Patient portal coming soon. Please contact support.'),
            default => $this->handleUnauthorizedRole($request),
        };
    }

    /**
     * Handle unauthorized role access
     */
    private function handleUnauthorizedRole(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->withErrors([
            'email' => 'Unauthorized access. Please contact the administrator.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
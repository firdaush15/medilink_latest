<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles  (one or more roles allowed)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // If not logged in → redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If user role not in allowed list → block access
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        // If allowed → continue request
        return $next($request);
    }
}

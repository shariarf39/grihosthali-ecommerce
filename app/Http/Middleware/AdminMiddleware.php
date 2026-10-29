<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->is_admin) {
            return redirect()->route('admin.login');
        }

        // A deactivated admin is signed out on their next request.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('admin.login')->withErrors(['email' => 'Your admin account has been deactivated.']);
        }

        return $next($request);
    }
}

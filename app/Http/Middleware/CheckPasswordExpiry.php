<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    public function handle(Request $request, Closure $next): Response
    {
        $days = config('auth.password_expiry_days', 0);
        if ($days <= 0) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $changedAt = $user->password_changed_at ?? $user->created_at;

            if ($changedAt && $changedAt->addDays($days)->isPast()) {
                if ($request->routeIs('profile') || $request->routeIs('profile.update')) {
                    return $next($request);
                }

                return redirect()->route('profile')
                    ->with('warning', 'Your password has expired. Please change it to continue.');
            }
        }

        return $next($request);
    }
}

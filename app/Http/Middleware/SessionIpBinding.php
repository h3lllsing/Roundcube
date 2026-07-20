<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionIpBinding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('session.ip_binding', false)) {
            return $next($request);
        }

        if (Auth::check()) {
            $sessionIp = $request->session()->get('_login_ip');
            if ($sessionIp && $sessionIp !== $request->ip()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                abort(403, 'Session IP changed. Please log in again.');
            }
        }

        return $next($request);
    }
}

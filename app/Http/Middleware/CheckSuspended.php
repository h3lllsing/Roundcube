<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuspended()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account suspended.'], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}

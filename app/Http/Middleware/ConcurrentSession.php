<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ConcurrentSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('session.concurrent_limit', false)) {
            return $next($request);
        }

        if (Auth::check()) {
            $userId = Auth::id();
            $currentSessionId = $request->session()->getId();

            $activeSessions = DB::table('sessions')
                ->where('user_id', $userId)
                ->where('id', '!=', $currentSessionId)
                ->count();

            if ($activeSessions > 0) {
                DB::table('sessions')
                    ->where('user_id', $userId)
                    ->where('id', '!=', $currentSessionId)
                    ->delete();
            }
        }

        return $next($request);
    }
}

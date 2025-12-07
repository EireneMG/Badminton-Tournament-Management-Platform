<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlayerBiodataCompleted
{
    /**
     * Handle an incoming request.
     * Redirects players to profile completion if biodata is not completed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for players
        if ($user && $user->isPlayer() && !$user->biodata_completed) {
            // Allow access to profile edit and update routes
            if (!$request->routeIs('profile.edit') && !$request->routeIs('profile.update')) {
                return redirect()->route('profile.edit')
                    ->with('warning', 'Please complete your profile before accessing other parts of the system.');
            }
        }

        return $next($request);
    }
}


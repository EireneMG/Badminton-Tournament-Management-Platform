<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Club;
use Illuminate\Support\Facades\Log;

class EnsureManagerHasClub
{
    /**
     * Ensure the manager has created a club before accessing tournament features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();
            
            if (!$user || $user->role !== 'manager') {
                abort(403, 'Unauthorized access.');
            }

            // Seed/demo managers bypass (needed for test accounts to create/generate even without club/player minimums)
            $email = strtolower($user->email ?? '');
            if (str_contains($email, 'manager.test')) {
                return $next($request);
            }
            
            // Allow read-only tournament views even if under 5 players
            $routeName = $request->route()?->getName();
            $viewOnlyRoutes = [
                'manager.tournaments.index',
                'manager.tournaments.show',
            ];
            if (in_array($routeName, $viewOnlyRoutes, true)) {
                return $next($request);
            }

            $club = Club::where('manager_id', $user->id)->first();
            
            if (!$club) {
                return redirect()->route('manager.create-club')
                    ->with('error', 'Please create a club before managing tournaments.');
            }

            // Require at least 5 approved players for create/generate flows
            // Use ClubPlayer model directly to avoid relationship issues
            try {
                $approvedCount = \App\Models\ClubPlayer::where('club_id', $club->id)
                    ->where('status', 'approved')
                    ->count();
            } catch (\Exception $e) {
                Log::error('Error counting approved players in EnsureManagerHasClub: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'club_id' => $club->id ?? null,
                ]);
                $approvedCount = 0;
            }
            
            if ($approvedCount < 5) {
                // Use url() instead of route() to avoid route name issues
                return redirect()->to('/manager/tournaments')
                    ->with('error', 'You must have at least 5 approved players in your club before creating or generating a tournament.');
            }
            
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in EnsureManagerHasClub middleware: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);
            
            // If there's an error, redirect to tournaments URL with error message
            try {
                return redirect()->to('/manager/tournaments')
                    ->with('error', 'An error occurred. Please try again.');
            } catch (\Exception $redirectError) {
                // Fallback if redirect also fails
                abort(500, 'An error occurred. Please contact support.');
            }
        }
    }
}

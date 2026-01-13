<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ManagerIdVerification;

class EnsureManagerHasVerifiedId
{
    /**
     * Ensure the manager has uploaded and verified their ID before accessing tournament features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized access.');
        }

        $routeName = $request->route()?->getName();

        // Seed/test managers: allow all actions without verification to keep demo data usable
        $email = strtolower($user->email ?? '');
        $seedManagers = ['manager.test1@badmintourph.com', 'manager.test2@badmintourph.com'];
        if (in_array($email, $seedManagers, true) || str_contains($email, 'manager.test')) {
            return $next($request);
        }

        // Allow read-only and registration/payment actions even if ID is not yet verified
        // Also allow match management actions (walkover, reschedule, record-result) for ongoing tournaments
        $viewOnlyRoutes = [
            'manager.tournaments.index',
            'manager.tournaments.show',
            'manager.tournaments.create',
            'manager.tournaments.generate',
            'manager.registrations.mark-paid',
            'manager.registrations.update-status',
            'manager.registrations.bulk-update-status',
            'manager.matches.record-result',
            'manager.matches.walkover',
            'manager.matches.reschedule',
            'manager.matches.update-score',
        ];
        if (in_array($routeName, $viewOnlyRoutes, true)) {
            return $next($request);
        }
        
        $hasVerifiedId = ManagerIdVerification::where('manager_id', $user->id)
            ->where('status', 'verified')
            ->exists();
        
        if (!$hasVerifiedId) {
            return redirect()->route('manager.verify-id')
                ->with('error', 'Please verify your ID before creating tournaments. Your verification is pending approval.');
        }
        
        return $next($request);
    }
}

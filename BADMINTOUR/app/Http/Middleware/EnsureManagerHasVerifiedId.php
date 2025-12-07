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

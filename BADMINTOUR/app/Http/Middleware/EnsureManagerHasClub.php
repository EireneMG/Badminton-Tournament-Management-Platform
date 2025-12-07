<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Club;

class EnsureManagerHasClub
{
    /**
     * Ensure the manager has created a club before accessing tournament features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'manager') {
            abort(403, 'Unauthorized access.');
        }
        
        $hasClub = Club::where('manager_id', $user->id)->exists();
        
        if (!$hasClub) {
            return redirect()->route('manager.create-club')
                ->with('error', 'Please create a club before managing tournaments.');
        }
        
        return $next($request);
    }
}

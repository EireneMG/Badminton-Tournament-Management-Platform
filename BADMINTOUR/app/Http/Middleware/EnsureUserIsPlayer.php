<?php

namespace App\http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Reponse;

class EnsureUserIsPlayer
{
    /**
     * Handle an incoming request.
     * 
     * @param \Closure(\illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Reponse
    {
        {
        if(!$request -> user() || !$request->user()->isPlayer()){
            abort(code: 403, message: 'Access denied. Player only.');
        }
        return $next($request);
    }

    }
    
}
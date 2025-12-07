<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PlayerLoginRequest;
use App\Http\Requests\Auth\ManagerLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the player login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming player authentication request.
     */
    public function store(PlayerLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $dashboardRoute = $request->user()->getDashboardRoute();

        return redirect()->intended(route($dashboardRoute, absolute: false));
    }

    /**
     * Display the manager login view.
     */
    public function createManager(): View
    {
        return view('auth.login-manager');
    }

    /**
     * Handle an incoming manager authentication request.
     */
    public function storeManager(ManagerLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $dashboardRoute = $request->user()->getDashboardRoute();

        return redirect()->intended(route($dashboardRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

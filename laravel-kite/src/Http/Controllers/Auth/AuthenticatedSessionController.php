<?php

namespace abenevaut\Kite\Http\Controllers\Auth;

use abenevaut\Infrastructure\Http\Controllers\ControllerAbstract;
use abenevaut\Kite\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthenticatedSessionController extends ControllerAbstract
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return \Inertia\Inertia::render('Auth/Login', [
            'canRegister' => Gate::has('can-register') && Gate::allows('can-register'),
            'canResetPassword' => Gate::has('can-reset-password') && Gate::allows('can-reset-password'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        \Illuminate\Support\Facades\Log::log('info', 'Login attempt', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $request->authenticate();

        $request->session()->regenerate();

        if (
            class_exists(\Inertia\Inertia::class)
            && $request->session()->has('url.intended')
        ) {
            return \Inertia\Inertia::location(session('url.intended'));
        }

        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

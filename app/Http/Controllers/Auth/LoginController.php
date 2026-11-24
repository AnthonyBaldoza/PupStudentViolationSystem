<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // is_active => true means deactivated accounts cannot log in.
        $attempt = Auth::attempt(
            [...$credentials, 'is_active' => true],
            $request->boolean('remember')
        );

        if (! $attempt) {
            return back()
                ->withErrors(['email' => 'Invalid email or password, or the account is inactive.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate(); // prevents session fixation

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

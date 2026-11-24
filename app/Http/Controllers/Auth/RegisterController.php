<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        abort_unless(config('registration.code'), 404);

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $code = config('registration.code');

        abort_unless($code, 404);

        // Check the code first so outsiders can't probe which emails already exist.
        if (! hash_equals((string) $code, (string) $request->input('registration_code'))) {
            return back()
                ->withErrors(['registration_code' => 'Invalid registration code.'])
                ->onlyInput('name', 'email');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // Self-registration can only ever create an Officer. Admin accounts are never
        // created here.
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
            'role' => User::ROLE_OFFICER,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
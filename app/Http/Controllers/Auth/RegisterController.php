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

        return view('auth.register', [
            'adminEnabled' => (bool) $this->adminCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $officerCode = config('registration.code');

        abort_unless($officerCode, 404);

        // Which codes exist for which role. Only these two roles can ever be created here.
        $codes = [
            User::ROLE_OFFICER => $officerCode,
            User::ROLE_ADMIN => $this->adminCode(),
        ];

        $role = (string) $request->input('role', User::ROLE_OFFICER);
        $expected = $codes[$role] ?? null; // null = unknown role, or admin signup disabled

        if (! $expected) {
            return back()
                ->withErrors(['role' => 'This account type is not available for registration.'])
                ->onlyInput('name', 'email', 'role');
        }

        // Check the code before anything else so outsiders can't probe which emails exist.
        if (! hash_equals((string) $expected, (string) $request->input('registration_code'))) {
            return back()
                ->withErrors(['registration_code' => 'Invalid registration code for the selected account type.'])
                ->onlyInput('name', 'email', 'role');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
            'role' => $role,                 // officer or admin; proven by the matching code above
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * The admin code, or null when admin signup is off. If someone set the admin
     * code equal to the officer code, admin signup is switched off: otherwise the
     * officer code would also unlock Admin accounts.
     */
    private function adminCode(): ?string
    {
        $admin = config('registration.admin_code');

        if (! $admin || $admin === config('registration.code')) {
            return null;
        }

        return (string) $admin;
    }
}
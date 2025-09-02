<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = $request->input('email');
        $password = $request->input('password');

        // Try web user login first
        if (Auth::attempt(['email' => $loginField, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Try game account login
        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
        $account = $isEmail ? 
            Account::where('email', $loginField)->first() :
            Account::where('login', $loginField)->first();

        if ($account && $this->verifyGamePassword($password, $account->password)) {
            if (!$account->isActive()) {
                throw ValidationException::withMessages([
                    'email' => 'Account is banned or suspended.'
                ]);
            }

            // Find or create web user
            $user = User::where('account_id', $account->id)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $account->login,
                    'email' => $account->email,
                    'password' => Hash::make($password),
                    'account_id' => $account->id,
                ]);
                $user->assignRole('player');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid credentials.'
        ]);
    }

    private function verifyGamePassword($password, $hashedPassword)
    {
        $gameHash = '*' . strtoupper(sha1(sha1($password, true)));
        return hash_equals($hashedPassword, $gameHash);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
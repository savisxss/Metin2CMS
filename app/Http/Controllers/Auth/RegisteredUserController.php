<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:web_users'],
            'username' => [
                'required', 
                'string', 
                'min:4', 
                'max:12', 
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) {
                    if (Account::where('login', $value)->exists()) {
                        $fail('Username already taken.');
                    }
                }
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'empire' => ['required', 'integer', 'in:1,2,3'],
        ]);

        DB::beginTransaction();

        try {
            // Create game account
            $account = new Account();
            $account->login = $request->username;
            $account->password = $request->password;
            $account->email = $request->email;
            $account->social_id = rand(1000000, 9999999);
            $account->status = 'OK';
            $account->empire = $request->empire;
            $account->create_time = now();
            $account->setConnection('mysql');
            $account->save();

            // Create safebox
            DB::connection('player')->table('safebox')->insert([
                'account_id' => $account->id,
                'size' => 135,
                'password' => '000000'
            ]);

            // Create web user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'account_id' => $account->id,
            ]);

            $user->assignRole('player');

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect(route('dashboard'))->with('success', 'Registration successful!');

        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'email' => 'Registration failed. Please try again.'
            ]);
        }
    }
}
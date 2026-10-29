<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $data['login'], 'password' => $data['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('account.index'));
        }

        return back()->withErrors(['login' => 'These credentials do not match our records.'])->onlyInput('login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(\+?88)?01[3-9]\d{8}$/', 'unique:users,phone'],
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], ['phone.regex' => 'Please enter a valid Bangladeshi mobile number.']);

        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index')->with('success', 'Welcome to ' . setting('site_name', 'Grihosthali') . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

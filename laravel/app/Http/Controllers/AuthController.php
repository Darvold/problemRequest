<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Обработка регистрации
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fio' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|in:dispatcher,master',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'fio' => $request->fio,
            'login' => $request->login,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email' => $request->login . '@example.com', // временный email
        ]);

        Auth::login($user);

        return redirect()->intended($this->redirectTo());
    }

    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Обработка входа
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended($this->redirectTo());
        }

        return back()->withErrors([
            'login' => 'Неверный логин или пароль.',
        ])->onlyInput('login.index');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Определить редирект после входа на основе роли
     */
    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->role === 'dispatcher') {
            return '/dispatcher/dashboard';
        } elseif ($user->role === 'master') {
            return '/master/dashboard';
        }

        return '/dashboard';
    }
}

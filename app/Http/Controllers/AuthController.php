<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===== ADMIN AUTH =====

    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()
                    ->with('error', 'Anda tidak memiliki akses ke admin panel')
                    ->withInput($request->only('email', 'password'));
            }

            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang Admin!');
        }

        return back()
            ->with('error', 'Email atau password salah')
            ->withInput($request->only('email', 'password'));
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Anda telah logout');
    }

    public function registerForm()
    {
        return view('admin.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'admin',
        ]);

        return redirect()->route('login')->with('success', 'Admin berhasil dibuat. Silakan login');
    }

    // ===== END =====
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCashierController extends Controller
{
    public function index(): View
    {
        $cashiers = User::query()
            ->where('is_cashier', true)
            ->latest()
            ->paginate(25);

        return view('admin.cashiers.index', compact('cashiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'is_cashier' => true,
        ]);

        return redirect()->route('admin.cashiers.index')->with('success', 'Akun kasir berhasil dibuat.');
    }
}

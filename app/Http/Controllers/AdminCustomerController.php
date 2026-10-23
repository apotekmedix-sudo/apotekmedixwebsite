<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->where('role', 'user')
            ->where('is_cashier', false)
            ->latest()
            ->paginate(25);

        return view('admin.customers.index', compact('customers'));
    }
}

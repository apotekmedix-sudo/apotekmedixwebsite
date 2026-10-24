<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isCashier())) {
            return redirect()->guest(route('cashier.login'))->with('error', 'Silakan masuk menggunakan akun admin atau kasir.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\ShoppingAccessCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalShoppingAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $expiresAt = (int) $request->session()->get('internal_shopping_expires_at', 0);
        $codeHash = $request->session()->get('internal_shopping_code_hash');
        if (
            $expiresAt <= now()->timestamp
            || !ShoppingAccessCode::matchesSession(ShoppingAccessCode::CHANNEL_INTERNAL, $codeHash)
        ) {
            $request->session()->forget(['internal_shopping_expires_at', 'internal_shopping_code_hash']);

            return redirect()->guest(route('shopping.internal.access'));
        }

        return $next($request);
    }
}

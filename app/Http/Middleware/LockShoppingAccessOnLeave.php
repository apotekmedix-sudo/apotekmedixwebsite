<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockShoppingAccessOnLeave
{
    private const INTERNAL_KEYS = ['internal_shopping_expires_at', 'internal_shopping_code_hash'];

    private const B2B_KEYS = ['pbf_access_expires_at', 'pbf_access_code_hash'];

    private const SHOPPING_FLOW_ROUTES = [
        'shopping.internal',
        'shopping.internal.access',
        'shopping.internal.verify',
        'shopping.internal.logout',
        'products.pbf',
        'products.pbf.verify',
        'products.pbf.logout',
        'products.stock-availability',
        'shopping.access.lock',
        'cart.index',
        'cart.page',
        'checkout.index',
        'checkout.validate-discount',
        'orders.history.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->routeIs(...self::SHOPPING_FLOW_ROUTES)) {
            $request->session()->forget([...self::INTERNAL_KEYS, ...self::B2B_KEYS]);
        } elseif ($request->routeIs('shopping.internal*')) {
            $request->session()->forget(self::B2B_KEYS);
        } elseif ($request->routeIs('products.pbf*')) {
            $request->session()->forget(self::INTERNAL_KEYS);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\ShoppingAccessCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockShoppingAccessOnLeave
{
    private const B2B_KEYS = ['pbf_access_expires_at', 'pbf_access_code_hash'];

    private const SHOPPING_FLOW_ROUTES = [
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
        $isB2bProductDetail = $request->routeIs('medicines.show')
            && $request->query('from') === 'b2b'
            && ShoppingAccessCode::hasActiveB2bSession();

        if (!$request->routeIs(...self::SHOPPING_FLOW_ROUTES) && !$isB2bProductDetail) {
            $request->session()->forget(self::B2B_KEYS);
        }

        return $next($request);
    }
}

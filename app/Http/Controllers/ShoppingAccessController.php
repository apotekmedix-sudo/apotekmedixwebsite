<?php

namespace App\Http\Controllers;

use App\Models\ShoppingAccessCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class ShoppingAccessController extends Controller
{
    public function lock(Request $request, string $channel)
    {
        abort_unless($channel === 'b2b', 404);

        $request->session()->forget(['pbf_access_expires_at', 'pbf_access_code_hash']);

        return response()->noContent();
    }
}

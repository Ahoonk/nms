<?php

namespace App\Http\Controllers;

use App\Services\WireGuard\WireGuardService;

class WireGuardController extends Controller
{
    public function peers(WireGuardService $wireGuard)
    {
        return response()->json(
            $wireGuard->peers()
        );
    }
}

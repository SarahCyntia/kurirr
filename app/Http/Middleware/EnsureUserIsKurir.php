<?php

// app/Http/Middleware/EnsureUserIsKurir.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsKurir
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role !== 'kurir') {
            return response()->json(['message' => 'Akses hanya untuk kurir'], 403);
        }

        return $next($request);
    }
}

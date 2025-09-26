<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!tenant()) {
            return response()->json([
                'message' => 'Invalid tenant. Please access this API through a valid tenant subdomain.',
            ], 403);
        }

        if (tenant()->status !== 'active') {
            return response()->json([
                'message' => 'This tenant account has been suspended or deactivated.',
            ], 403);
        }

        return $next($request);
    }
}
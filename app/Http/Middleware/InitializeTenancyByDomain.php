<?php

namespace App\Http\Middleware;

use Closure;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseMiddleware;

class InitializeTenancyByDomain extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        return parent::handle($request, $next);
    }
}
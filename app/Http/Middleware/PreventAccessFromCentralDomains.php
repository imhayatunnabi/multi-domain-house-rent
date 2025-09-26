<?php

namespace App\Http\Middleware;

use Closure;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains as BaseMiddleware;

class PreventAccessFromCentralDomains extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        return parent::handle($request, $next);
    }
}
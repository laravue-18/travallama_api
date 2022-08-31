<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class SetSanctumGuard
{
    public function handle($request, Closure $next)
    {
        if (Str::startsWith($request->getRequestUri(), '/api/admin/')) {
            config(['sanctum.guard' => 'admin']);
        } elseif (Str::startsWith($request->getRequestUri(), '/api/manager/')) {
            config(['sanctum.guard' => 'manager']);
        } elseif (Str::startsWith($request->getRequestUri(), '/api/customer/')) {
            config(['sanctum.guard' => 'customer']);
        }

        return $next($request);
    }
}

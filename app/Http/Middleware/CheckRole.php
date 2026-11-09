<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, $roles, true)) {
            abort(403, 'Unauthorized access for your role.');
        }

        return $next($request);
    }
}

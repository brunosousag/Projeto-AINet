<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->blocked) {
            abort(403, 'Your account is blocked. Please contact support.');
        }

        return $next($request);
    }
}

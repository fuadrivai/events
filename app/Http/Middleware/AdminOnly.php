<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next)
    {
        $user = auth()->user();
        if (! $user || (! $user->is_admin && ! $user->isAdmin())) {
            abort(403);
        }

        return $next($request);
    }
}

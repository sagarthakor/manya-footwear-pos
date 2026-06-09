<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($role === 'super_admin' && $user->isSuperAdmin()) return $next($request);
            if ($role === 'admin'       && $user->isAdmin())      return $next($request);
            if ($role === 'manager'     && $user->isManager())    return $next($request);
            if ($role === $user->role)                            return $next($request);
        }

        abort(403, 'Aapko yahan access nahi hai.');
    }
}

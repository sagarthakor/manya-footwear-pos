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

        // Super admin ko kisi bhi role restriction se bypass milti hai
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Aapko yahan access nahi hai.');
        }

        return $next($request);
    }
}

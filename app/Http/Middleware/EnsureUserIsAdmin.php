<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        $user = Auth::user();

        $roleName = $user->role->name ?? $user->role ?? null;

        if ($user && $roleName === 'Admin') {
            return $next($request);
        }
        else if ($user && $roleName === 'Superadmin') {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}

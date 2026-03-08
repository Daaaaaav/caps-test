<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAgent
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user is an agent
        if (auth()->user() && auth()->user()->is_agent !== 'yes') {
            // Redirect if the user is not an agent
            return redirect()->route('home')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }
}


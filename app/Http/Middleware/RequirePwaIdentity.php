<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePwaIdentity
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->pwaPreference?->email_verified_at) {
            return redirect()->route('app.signin');
        }
        return $next($request);
    }
}
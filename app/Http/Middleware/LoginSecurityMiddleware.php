<?php

namespace App\Http\Middleware;

use App\Support\Google2FAAuthenticator;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class LoginSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authenticator = app(Google2FAAuthenticator::class)->boot($request);

        $sidebar_key = Auth::user()->getSidebarKey();
        if ($authenticator->isAuthenticated()) {
            Session::put($sidebar_key, true);
            return $next($request);
        }
        
        Session::put($sidebar_key, false);
        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
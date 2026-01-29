<?php

namespace AdaReach\Sms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if authentication is disabled
        if (!config('adarearch.dashboard.auth_enabled', true)) {
            return $next($request);
        }

        // Check if user is already authenticated
        if ($request->session()->has('adarearch_authenticated')) {
            return $next($request);
        }

        // Allow access to login routes
        if ($request->is('*/login') || $request->is('*/logout')) {
            return $next($request);
        }

        // Redirect to login if not authenticated
        return redirect(config('adarearch.dashboard.path', 'sms-dashboard') . '/login');
    }
}

<?php

namespace AdaReach\Sms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardAuthorization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Add your authorization logic here
        // For now, we'll allow access if the dashboard is enabled
        if (!config('adarearch.dashboard.enabled', true)) {
            abort(403, 'SMS Dashboard is disabled');
        }

        return $next($request);
    }
}

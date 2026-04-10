<?php

namespace App\Http\Middleware;

use Closure;

class ErrorNotificationMiddleware
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
        $response = $next($request);

        // Check if there's any error message in the session
        if (session()->has('error')) {
            // Add the error message to the response so the frontend can display it
            $response->headers->set('X-Error-Message', session('error'));
        }

        return $response;
    }
}

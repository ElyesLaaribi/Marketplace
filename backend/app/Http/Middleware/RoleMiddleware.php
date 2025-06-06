<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         // If not logged in, return unauthorized
         if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the authenticated user has the required role
        if (Auth::user()->role !== $role) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}

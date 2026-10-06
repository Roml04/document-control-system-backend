<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSysAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user()->role !== UserRole::SysAdmin->value) {
          return response()->json([
            "message" => "Restricted access",
            "role" => $request->user()->role === UserRole::SysAdmin
          ], 403);
        }

        return $next($request);
    }
}

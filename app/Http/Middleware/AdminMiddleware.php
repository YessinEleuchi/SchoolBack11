<?php
namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === RoleEnum::Admin->value) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Only admins can access this resource.',
        ], 403);
    }
}
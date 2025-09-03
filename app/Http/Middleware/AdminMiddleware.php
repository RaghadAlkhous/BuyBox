<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // التحقق إذا كان المستخدم الحالي مديرًا
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized access. Admins only.'], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->hasAnyRole($roles)) {
            abort(403, 'អ្នកមិនមានសិទ្ធិចូលប្រើទំព័រនេះទេ។');
        }

        return $next($request);
    }
}

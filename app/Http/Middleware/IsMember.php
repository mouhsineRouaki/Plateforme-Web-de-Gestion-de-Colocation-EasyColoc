<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMember
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403);
        }

        $user = auth()->user();

        $hasMemberRole = $user->colocations()
            ->wherePivot('role_in_colocation', 'MEMBER')
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->exists();

        if (! $hasMemberRole) {
            abort(403);
        }

        return $next($request);
    }
}

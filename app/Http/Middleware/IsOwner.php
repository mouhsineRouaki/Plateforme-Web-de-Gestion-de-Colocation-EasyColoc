<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403);
        }

        $user = auth()->user();

        $ownerRole = $user->colocations()
            ->wherePivot('role_in_colocation', 'OWNER')
            ->wherePivotNull('left_at')
            ->where('status', 'ACTIVE')
            ->exists();

        if (! $ownerRole) {
            abort(403);
        }

        return $next($request);
    }
}

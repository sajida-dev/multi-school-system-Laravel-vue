<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TeamsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user && $user->hasRole('superadmin')) {
            setPermissionsTeamId(null);
        } else {
            $teamId = session('active_school_id');

            // Optional fallback
            if (!$teamId && $user && $user->last_school_id) {
                $teamId = $user->last_school_id;
                session(['active_school_id' => $teamId]);
            }

            setPermissionsTeamId($teamId);
        }

        return $next($request);
    }
}

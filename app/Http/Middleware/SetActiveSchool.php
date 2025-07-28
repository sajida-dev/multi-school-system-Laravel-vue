<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Schools\app\Models\School;

class SetActiveSchool
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->hasRole('superadmin')) {
                $schools = \Modules\Schools\App\Models\School::all();
                $activeSchoolId = session('active_school_id');
                // Use last_school_id if present and valid
                if (!$activeSchoolId && $user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                    session(['active_school_id' => $user->last_school_id]);
                } elseif ($schools->count() > 0 && !$activeSchoolId) {
                    session(['active_school_id' => $schools->first()->id]);
                }
            } elseif ($user->hasRole('admin')) {
                $schools = $user->schools;
                $activeSchoolId = session('active_school_id');
                // Use last_school_id if present and valid
                if (!$activeSchoolId && $user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                    session(['active_school_id' => $user->last_school_id]);
                } elseif ($schools->count() > 0 && !$activeSchoolId) {
                    session(['active_school_id' => $schools->first()->id]);
                }
            }
        }
        return $next($request);
    }
}

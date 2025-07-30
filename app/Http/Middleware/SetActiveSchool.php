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
            $activeSchoolId = session('active_school_id');

            // If no active school is set in session, try to set one
            if (!$activeSchoolId) {
                if ($user->hasRole('superadmin')) {
                    $schools = School::all();

                    // Use last_school_id if present and valid
                    if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                        session(['active_school_id' => $user->last_school_id]);
                    } elseif ($schools->count() > 0) {
                        // Set to first available school
                        session(['active_school_id' => $schools->first()->id]);
                    }
                } elseif ($user->hasRole('admin')) {
                    $schools = $user->schools;

                    // Use last_school_id if present and valid
                    if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                        session(['active_school_id' => $user->last_school_id]);
                    } elseif ($schools->count() > 0) {
                        // Set to first available school
                        session(['active_school_id' => $schools->first()->id]);
                    }
                }
            }
        }
        return $next($request);
    }
}

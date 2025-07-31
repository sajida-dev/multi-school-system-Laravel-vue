<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Schools\app\Models\School;

class SetActiveSchool
{
    public function handle($request, Closure $next)
    {
        try {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $activeSchoolId = session('active_school_id');

                Log::info('SetActiveSchool middleware', [
                    'user_id' => $user->id,
                    'user_roles' => $user->roles->pluck('name'),
                    'active_school_id' => $activeSchoolId,
                    'last_school_id' => $user->last_school_id
                ]);

                // If no active school is set in session, try to set one
                if (!$activeSchoolId) {
                    if ($user->hasRole('superadmin')) {
                        $schools = School::all();
                        Log::info('Superadmin schools found', ['count' => $schools->count()]);

                        // Use last_school_id if present and valid
                        if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                            session(['active_school_id' => $user->last_school_id]);
                            Log::info('Set active school from last_school_id', ['school_id' => $user->last_school_id]);
                        } elseif ($schools->count() > 0) {
                            // Set to first available school
                            $firstSchool = $schools->first();
                            session(['active_school_id' => $firstSchool->id]);
                            Log::info('Set active school to first available', ['school_id' => $firstSchool->id]);
                        } else {
                            Log::warning('No schools available for superadmin');
                        }
                    } elseif ($user->hasRole('admin')) {
                        $schools = $user->schools;
                        Log::info('Admin schools found', ['count' => $schools->count()]);

                        // Use last_school_id if present and valid
                        if ($user->last_school_id && $schools->contains('id', $user->last_school_id)) {
                            session(['active_school_id' => $user->last_school_id]);
                            Log::info('Set active school from last_school_id', ['school_id' => $user->last_school_id]);
                        } elseif ($schools->count() > 0) {
                            // Set to first available school
                            $firstSchool = $schools->first();
                            session(['active_school_id' => $firstSchool->id]);
                            Log::info('Set active school to first available', ['school_id' => $firstSchool->id]);
                        } else {
                            Log::warning('No schools available for admin', ['user_id' => $user->id]);
                        }
                    } else {
                        Log::info('User has no role requiring school selection', [
                            'user_id' => $user->id,
                            'roles' => $user->roles->pluck('name')
                        ]);
                    }
                }
            } else {
                Log::info('User not authenticated in SetActiveSchool middleware');
            }
        } catch (\Exception $e) {
            Log::error('SetActiveSchool middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $next($request);
    }
}

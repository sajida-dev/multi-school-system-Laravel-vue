<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Schools\App\Models\School;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display the user management page with initial data
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', '');

        $query = User::with('roles')
            ->select('id', 'name', 'email', 'created_at');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($roleFilter) {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Get all available roles for filter dropdown
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('settings/Users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'per_page' => $perPage
            ]
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);
        $schoolId = $request->school_id;

        // Check if user is trying to assign admin role to themselves
        if ($request->user()->id === $user->id && $role->name === 'superadmin') {
            return redirect()->back()->with('error', 'You cannot assign admin role to yourself');
        }

        // Add role to user (don't sync, just add)
        $user->assignRole($role->name, $schoolId);

        $school = School::find($schoolId);
        return redirect()->back()->with('success', "Role '{$role->name}' assigned to {$user->name} for {$school->name} successfully");
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        // Check if user is trying to remove admin role from themselves
        if ($request->user()->id === $user->id && $role->name === 'superadmin') {
            return redirect()->back()->with('error', 'You cannot remove admin role from yourself');
        }

        // Check if this is the last admin user
        if ($role->name === 'superadmin' && $user->hasRole('superadmin')) {
            $adminCount = User::role('superadmin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'Cannot remove the last admin user');
            }
        }

        $user->removeRole($role->name);

        return redirect()->back()->with('success', "Role '{$role->name}' removed from {$user->name} successfully");
    }
}

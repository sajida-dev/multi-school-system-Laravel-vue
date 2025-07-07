<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load permissions for each role
        return response()->json(
            \Spatie\Permission\Models\Role::with('permissions')->get()
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'nullable|string',
        ]);
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Role created successfully!',
                'role' => $role
            ]);
        }
        if ($request->expectsJson() && !$request->hasHeader('X-Inertia')) {
            return response()->json($role, 201);
        }
        return redirect()->back()->with([
            'success' => 'Role created successfully!',
            'role' => $role
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? $role->guard_name,
        ]);
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }
        $role->load('permissions');

        // Always check for Inertia first, and never return JSON for Inertia
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Permissions updated successfully!',
                'role' => $role
            ]);
        }

        // Only return JSON for true API requests (no X-Inertia)
        if ($request->expectsJson() && !$request->hasHeader('X-Inertia')) {
            return response()->json($role);
        }

        // Fallback: always redirect for web requests
        return redirect()->back()->with([
            'success' => 'Permissions updated successfully!',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        if (request()->hasHeader('X-Inertia')) {
            return redirect()->back()->with([
                'success' => 'Role deleted successfully!'
            ]);
        }
        if (request()->expectsJson() && !request()->hasHeader('X-Inertia')) {
            return response()->json(['message' => 'Role deleted']);
        }
        return redirect()->back()->with([
            'success' => 'Role deleted successfully!'
        ]);
    }
}

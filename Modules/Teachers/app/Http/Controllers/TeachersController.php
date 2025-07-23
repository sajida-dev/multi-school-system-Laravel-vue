<?php

namespace Modules\Teachers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Modules\Teachers\Models\Teacher;
use Spatie\Permission\Models\Role;

class TeachersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['teacher', 'principal']);
            })
            ->with(['teacher', 'roles']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($tq) use ($search) {
                        $tq->where('cnic', 'like', "%{$search}%")
                            ->orWhere('contact_no', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        if ($request->filled('gender')) {
            $query->whereHas('teacher', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }
        $perPage = $request->input('per_page', 12);
        $users = $query->orderBy('name')->paginate($perPage);

        return Inertia::render('Teachers/Index', [
            'teachers' => $users,
            'filters' => $request->only(['search', 'role', 'gender']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schoolId = $request->input('school_id');
        $roles = Role::whereIn('name', ['teacher', 'principal'])
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_id');
                if ($schoolId) {
                    $q->orWhere('school_id', $schoolId);
                }
            })
            ->get(['id', 'name']);
        return Inertia::render('Teachers/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|alpha_dash|unique:users,username',
            'password' => 'required|string|min:8',
            'cnic' => 'required|string|unique:teachers,cnic',
            'gender' => 'required|in:Male,Female',
            'marital_status' => 'required|in:Single,Married',
            'role_id' => ['required', 'exists:roles,id'],
            'dob' => 'required|date',
            'salary' => 'required|numeric',
            'phone_number' => 'required|string',
            'date_of_joining' => 'required|date',
            'experience_years' => 'nullable|integer',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'nullable|exists:classes,id',
        ]);



        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
            ]);
            $role = Role::findOrFail($validated['role_id']);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($validated['school_id']);
            $user->assignRole($role->name);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
            $teacher = new Teacher([
                'user_id' => $user->id,
                'school_id' => $validated['school_id'],
                'cnic' => $validated['cnic'],
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'],
                'role_id' => $role->id,
                'dob' => $validated['dob'],
                'salary' => $validated['salary'],
                'date_of_joining' => $validated['date_of_joining'],
                'experience_years' => $validated['experience_years'],
                'class_id' => $validated['class_id'] ?? null,
            ]);
            $teacher->save();
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher added successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $user = User::with(['teacher', 'roles'])->findOrFail($id);
        return Inertia::render('Teachers/Show', [
            'teacher' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        $user = User::with(['teacher', 'roles'])->findOrFail($id);
        $schoolId = $request->input('school_id') ?? $user->teacher->school_id ?? null;
        $roles = Role::whereIn('name', ['teacher', 'principal'])
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_id');
                if ($schoolId) {
                    $q->orWhere('school_id', $schoolId);
                }
            })
            ->get(['id', 'name']);
        return Inertia::render('Teachers/Edit', [
            'teacher' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|alpha_dash|unique:users,username,' . $id,
            'gender' => 'required|in:Male,Female',
            'marital_status' => 'required|in:Single,Married',
            'role_id' => ['required', 'exists:roles,id'],
            'dob' => 'required|date',
            'salary' => 'required|numeric',
            'phone_number' => 'required|string',
            'date_of_joining' => 'required|date',
            'experience_years' => 'nullable|integer',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $user = User::findOrFail($id);
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
            ]);
            $role = Role::findOrFail($validated['role_id']);
            $user->syncRoles([$role->name]);
            $teacher = $user->teacher;
            $teacher->update([
                'school_id' => $validated['school_id'],
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'],
                'role_id' => $role->id,
                'dob' => $validated['dob'],
                'salary' => $validated['salary'],
                'date_of_joining' => $validated['date_of_joining'],
                'experience_years' => $validated['experience_years'],
                'class_id' => $validated['class_id'] ?? null,
            ]);
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            if ($user->teacher) {
                $user->teacher->delete();
            }
            $user->delete();
        });
        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}

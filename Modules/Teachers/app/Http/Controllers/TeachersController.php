<?php

namespace Modules\Teachers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPassword;
use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Modules\Teachers\Models\Teacher;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TeachersController extends Controller
{
    /**
     * Helper method to set school context for role operations
     */
    private function setSchoolContextForRoles($schoolId)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
    }

    /**
     * Helper method to clear school context
     */
    private function clearSchoolContext()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');
        if ($schoolId) {
            $this->setSchoolContextForRoles($schoolId);
        }
        $query = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['teacher', 'principal']);
            })
            ->whereHas('teacher', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with(['teacher.school', 'teacher.role', 'teacher.class', 'roles']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($tq) use ($search) {
                        $tq->where('cnic', 'like', "%{$search}%");
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
        if ($request->filled('status')) {
            $query->whereHas('teacher', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        $perPage = $request->input('per_page', 12);
        $users = $query->orderBy('name')->paginate($perPage);

        // Optionally clear the team context after
        $this->clearSchoolContext();

        return Inertia::render('Teachers/Index', [
            'teachers' => $users,
            'filters' => $request->only(['search', 'role', 'gender', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Use the global selected school from session
        $schoolId = session('active_school_id');

        // If no school in session, try to get from user's last_school_id
        if (!$schoolId) {
            /** @var User $user */
            $user = Auth::user();
            if ($user && $user->last_school_id) {
                $schoolId = $user->last_school_id;
                // Set it in session for future use
                session(['active_school_id' => $schoolId]);
            }
        }

        // If still no school, try to get the first available school for the user
        if (!$schoolId) {
            /** @var User $user */
            $user = Auth::user();
            if ($user) {
                // Check if user has superadmin role
                if ($user->roles()->where('name', 'superadmin')->exists()) {
                    $firstSchool = \Modules\Schools\App\Models\School::first();
                    if ($firstSchool) {
                        $schoolId = $firstSchool->id;
                        session(['active_school_id' => $schoolId]);
                    }
                } elseif ($user->roles()->where('name', 'admin')->exists()) {
                    // For admin users, get their associated schools
                    $firstSchool = $user->schools()->first();
                    if ($firstSchool) {
                        $schoolId = $firstSchool->id;
                        session(['active_school_id' => $schoolId]);
                    }
                }
            }
        }

        // If still no school found, show a proper error message
        if (!$schoolId) {
            return redirect()->back()->withErrors(['error' => 'No school is available. Please contact an administrator to set up schools.']);
        }

        // Get roles for the selected school (including global roles)
        $roles = Role::whereIn('name', ['teacher', 'principal'])
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('school_id'); // Global roles
                $q->orWhere('school_id', $schoolId); // School-specific roles
            })
            ->get(['id', 'name']);

        // Check if any roles are available
        $hasRoles = $roles->count() > 0;

        if (!$hasRoles) {
            return redirect()->back()->withErrors(['error' => 'No teacher or principal roles are available for the selected school. Please contact an administrator to create the necessary roles.']);
        }

        return Inertia::render('Teachers/Create', [
            'roles' => $roles,
            'hasRoles' => $hasRoles,
            'selectedSchoolId' => $schoolId,
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
            'profile_photo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'username' => $validated['username'],
                    'phone_number' => $validated['phone_number'],
                ];

                // Handle profile photo upload
                if ($request->hasFile('profile_photo')) {
                    $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
                    $userData['profile_photo_path'] = $photoPath;
                }

                $user = PasswordService::createUserWithPassword($userData, $validated['password']);

                // Get the role and validate it exists
                $role = Role::findOrFail($validated['role_id']);

                // Validate that the role exists for the specific school
                $this->setSchoolContextForRoles($validated['school_id']);

                // Check if the role exists in the current school context
                if (!Role::where('name', $role->name)->where('school_id', $validated['school_id'])->exists()) {
                    $this->clearSchoolContext();
                    throw new \Exception("Role '{$role->name}' does not exist for the selected school.");
                }

                try {
                    // Assign role with proper error handling
                    $user->assignRole($role->name);
                } catch (\Exception $e) {
                    // Log the error for debugging
                    Log::error('Role assignment failed during teacher creation', [
                        'user_id' => $user->id,
                        'role_id' => $validated['role_id'],
                        'role_name' => $role->name,
                        'school_id' => $validated['school_id'],
                        'error' => $e->getMessage()
                    ]);

                    // Clear the team context
                    $this->clearSchoolContext();

                    // Delete the user and throw error
                    $user->delete();
                    throw new \Exception('Failed to assign role. Please try again or contact support.');
                }

                // Clear the team context
                $this->clearSchoolContext();

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
                    'status' => 'pending', // Set status to pending by default
                ]);
                $teacher->save();
            });

            return redirect()->route('teachers.index')->with('success', 'Teacher added successfully.');
        } catch (\Exception $e) {
            Log::error('Teacher creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create teacher: ' . $e->getMessage()]);
        }
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
            'cnic' => 'required|string|unique:teachers,cnic,' . $id,
            'marital_status' => 'required|in:Single,Married',
            'role_id' => ['required', 'exists:roles,id'],
            'dob' => 'required|date',
            'salary' => 'required|numeric',
            'phone_number' => 'required|string',
            'date_of_joining' => 'required|date',
            'experience_years' => 'nullable|integer',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'nullable|exists:classes,id',
            'profile_photo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        try {
            DB::transaction(function () use ($validated, $id, $request) {
                $user = User::findOrFail($id);

                $userUpdateData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'username' => $validated['username'],
                    'phone_number' => $validated['phone_number'],
                ];

                // Handle profile photo upload
                if ($request->hasFile('profile_photo')) {
                    // Delete old profile photo if it exists and is not the default
                    if ($user->profile_photo_path && $user->profile_photo_path !== 'default-profile.png') {
                        Storage::disk('public')->delete($user->profile_photo_path);
                    }

                    // Store new profile photo
                    $photoPath = $request->file('profile_photo')->store('profile-photos', 'public');
                    $userUpdateData['profile_photo_path'] = $photoPath;
                }

                $user->update($userUpdateData);

                // Get the role and validate it exists
                $role = Role::findOrFail($validated['role_id']);

                // Validate that the role exists for the specific school
                $this->setSchoolContextForRoles($validated['school_id']);

                // Check if the role exists in the current school context
                if (!Role::where('name', $role->name)->where('school_id', $validated['school_id'])->exists()) {
                    $this->clearSchoolContext();
                    throw new \Exception("Role '{$role->name}' does not exist for the selected school.");
                }

                try {
                    // Remove all existing roles first
                    $user->syncRoles([]);

                    // Assign the new role
                    $user->assignRole($role->name);
                } catch (\Exception $e) {
                    // Log the error for debugging
                    Log::error('Role assignment failed', [
                        'user_id' => $user->id,
                        'role_id' => $validated['role_id'],
                        'role_name' => $role->name,
                        'school_id' => $validated['school_id'],
                        'error' => $e->getMessage()
                    ]);

                    // Clear the team context
                    $this->clearSchoolContext();

                    throw new \Exception('Failed to assign role. Please try again or contact support.');
                }

                // Clear the team context
                $this->clearSchoolContext();

                $teacher = $user->teacher;
                $updateData = [
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
                ];
                if (isset($validated['status'])) {
                    $updateData['status'] = $validated['status'];
                }
                $teacher->update($updateData);
            });

            return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
        } catch (\Exception $e) {
            Log::error('Teacher update failed', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to update teacher: ' . $e->getMessage()]);
        }
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

    /**
     * Securely return decrypted password for a teacher (admin/superadmin only)
     */
    public function getTeacherPassword(Request $request, $id = null)
    {
        $userId = $id ?? $request->input('user_id');
        $admin = $request->user();
        if (!$admin) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        // Only allow superadmin or admin
        if (!$admin->hasRole('superadmin') && !$admin->hasRole('admin')) {
            return redirect()->back()->with('error', 'Forbidden');
        }

        $result = PasswordService::getUserPassword($userId);

        if (!$result['success']) {
            // Return an Inertia partial response for SPA fetch
            if ($request->hasHeader('X-Inertia')) {
                return redirect()->back()->with('passwordResponse', ['error' => $result['error']]);
            }
            return redirect()->back()->with('error', $result['error']);
        }

        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with('passwordResponse', ['password' => $result['password']]);
        }
        return redirect()->back();
    }

    /**
     * Approve a teacher (admin/superadmin only)
     */
    public function approveTeacher($id)
    {
        $admin = request()->user();
        if (!$admin || (!$admin->hasRole('superadmin') && !$admin->hasRole('admin'))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Find the teacher record by user_id
        $teacher = Teacher::where('user_id', $id)->first();

        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        $teacher->status = 'approved';
        $teacher->save();

        return redirect()->back()->with('success', 'Teacher approved successfully.');
    }
}

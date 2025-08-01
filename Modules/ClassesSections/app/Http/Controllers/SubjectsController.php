<?php

namespace Modules\ClassesSections\App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ClassesSections\App\Models\Subject;
use Modules\ClassesSections\App\Models\ClassModel;
use App\Models\User;
use Inertia\Inertia;
use Modules\ClassesSections\app\Models\ClassSubject;
use Modules\Teachers\Models\ClassSubjectTeacher;
use Modules\Teachers\Models\Teacher;

class SubjectsController extends Controller
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

    public function index(Request $request)
    {
        $subjects = Subject::query()->orderBy('name')->get();

        // Get active school ID from session, fallback to first school if not set
        $activeSchoolId = session('active_school_id');

        // If no active school in session, get the first school
        if (!$activeSchoolId) {
            $firstSchool = \Modules\Schools\App\Models\School::first();
            if ($firstSchool) {
                $activeSchoolId = $firstSchool->id;
                // Set it in session for future requests
                session(['active_school_id' => $activeSchoolId]);
            }
        }

        // Get classes for the active school using Eloquent
        $classes = ClassModel::forSchool($activeSchoolId)->orderBy('name')->get();

        // Set school context for role operations
        $this->setSchoolContextForRoles($activeSchoolId);

        // Get teachers for the active school using Eloquent
        $teachers = User::whereHas('teacher', function ($query) use ($activeSchoolId) {
            $query->forSchool($activeSchoolId);
        })->whereHas('roles', function ($query) {
            $query->whereIn('name', ['teacher', 'principal']);
        })->with(['teacher.class', 'roles'])->get();

        // If no teachers found with school context, try without it
        if ($teachers->count() === 0) {
            Log::warning('No teachers found with school context, trying without context', [
                'active_school_id' => $activeSchoolId
            ]);

            $this->clearSchoolContext();

            // Try getting teachers without school context
            $teachers = User::whereHas('teacher', function ($query) use ($activeSchoolId) {
                $query->forSchool($activeSchoolId);
            })->whereHas('roles', function ($query) {
                $query->whereIn('name', ['teacher', 'principal']);
            })->with(['teacher.class', 'roles'])->get();

            Log::info('Teachers found without school context', [
                'teachers_count' => $teachers->count()
            ]);
        }

        // Add debugging
        Log::info('Teachers loaded for subject assignment', [
            'active_school_id' => $activeSchoolId,
            'teachers_count' => $teachers->count(),
            'teachers' => $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'roles' => $teacher->roles->pluck('name'),
                    'teacher_record' => $teacher->teacher ? [
                        'id' => $teacher->teacher->id,
                        'school_id' => $teacher->teacher->school_id,
                        'status' => $teacher->teacher->status,
                        'class_id' => $teacher->teacher->class_id,
                        'class_name' => $teacher->teacher->class ? $teacher->teacher->class->name : null
                    ] : null
                ];
            })
        ]);

        // Clear school context
        $this->clearSchoolContext();

        // Get assignments data using Eloquent
        $assignments = ClassModel::getWithSubjectsForSchool($activeSchoolId)
            ->flatMap(function ($class) {
                return $class->subjects->map(function ($subject) use ($class) {
                    return [
                        'class_id' => $class->id,
                        'class_name' => $class->name,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code,
                        'school_id' => $subject->pivot->school_id
                    ];
                });
            });

        // Load teacher assignments using Eloquent model
        $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
            ->forSchool($activeSchoolId)
            ->get()
            ->map(function ($assignment) {
                return [
                    'teacher_id' => $assignment->teacher_id,
                    'teacher_user_id' => $assignment->teacher->user_id,
                    'teacher_name' => $assignment->teacher->user->name,
                    'teacher_class_id' => $assignment->teacher->class_id,
                    'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                    'class_id' => $assignment->class_id,
                    'class_name' => $assignment->class->name,
                    'subject_id' => $assignment->subject_id,
                    'subject_name' => $assignment->subject->name,
                    'subject_code' => $assignment->subject->code,
                    'school_id' => $assignment->school_id
                ];
            });

        // Debug logging for teacher assignments
        Log::info('Teacher assignments loaded in index', [
            'active_school_id' => $activeSchoolId,
            'assignments_count' => $teacherAssignments->count(),
            'assignments' => $teacherAssignments->toArray()
        ]);

        // Get assignments data - Simplified: Use school_id directly
        // $assignments = DB::table('class_subject')
        //     ->join('classes', 'class_subject.class_id', '=', 'classes.id')
        //     ->join('subjects', 'class_subject.subject_id', '=', 'subjects.id')
        //     ->where('class_subject.school_id', $activeSchoolId)
        //     ->select(
        //         'class_subject.*',
        //         'classes.name as class_name',
        //         'subjects.name as subject_name',
        //         'subjects.code as subject_code'
        //     )
        //     ->get();

        // Only load teacher assignments if the request is for the teacher assignment tab
        // $teacherAssignments = collect([]);
        // if ($request->has('tab') && $request->tab === 'assign-teachers') {
        //     $teacherAssignments = DB::table('class_subject_teacher')
        //         ->join('teachers', 'class_subject_teacher.teacher_id', '=', 'teachers.id')
        //         ->join('users', 'teachers.user_id', '=', 'users.id')
        //         ->join('classes', 'class_subject_teacher.class_id', '=', 'classes.id')
        //         ->join('subjects', 'class_subject_teacher.subject_id', '=', 'subjects.id')
        //         ->where('class_subject_teacher.school_id', $activeSchoolId)
        //         ->select(
        //             'class_subject_teacher.*',
        //             'classes.name as class_name',
        //             'subjects.name as subject_name',
        //             'subjects.code as subject_code',
        //             'users.name as teacher_name',
        //             'users.id as teacher_user_id'
        //         )
        //         ->get();
        // }

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
            'classes' => $classes,
            'teachers' => $teachers,
            'assignments' => $assignments,
            'teacherAssignments' => $teacherAssignments,
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Subject creation request received', $request->all());

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:subjects,code',
                'description' => 'nullable|string|max:1000',
            ]);

            $subject = Subject::create($validated);

            Log::info('Subject created successfully', ['subject_id' => $subject->id]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subject created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to create subject. Please try again.']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Subject update request received', ['subject_id' => $id, 'data' => $request->all()]);

            $subject = Subject::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:subjects,code,' . $id,
                'description' => 'nullable|string|max:1000',
            ]);

            $subject->update($validated);

            Log::info('Subject updated successfully', ['subject_id' => $subject->id]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subject updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to update subject. Please try again.']);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Subject deletion request received', ['subject_id' => $id]);

            $subject = Subject::findOrFail($id);
            $subject->delete();

            Log::info('Subject deleted successfully', ['subject_id' => $subject->id]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subject deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete subject. Please try again.']);
        }
    }

    public function show($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            return Inertia::render('Subjects/Show', [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Subject not found.']);
        }
    }

    public function create()
    {
        return Inertia::render('Subjects/Create');
    }

    public function edit($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            return Inertia::render('Subjects/Edit', [
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Subject not found.']);
        }
    }

    public function assignToClass(Request $request, $classId)
    {
        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        try {
            // Get the active school ID
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No school is currently selected.']);
            }

            // First, remove all existing assignments for this class
            ClassSubject::where('class_id', $classId)
                ->where('school_id', $activeSchoolId)
                ->delete();

            // Then, create new assignments
            $assignments = [];
            foreach ($request->subject_ids as $subjectId) {
                $assignments[] = [
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'school_id' => $activeSchoolId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($assignments)) {
                ClassSubject::insert($assignments);
            }

            Log::info('Subjects assigned to class', [
                'class_id' => $classId,
                'subject_ids' => $request->subject_ids,
                'school_id' => $activeSchoolId
            ]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subjects assigned to class successfully!');
        } catch (\Exception $e) {
            Log::error('Error assigning subjects to class', [
                'error' => $e->getMessage(),
                'class_id' => $classId
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to assign subjects: ' . $e->getMessage()]);
        }
    }

    public function assignToTeacher(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:users,id'
        ]);

        try {
            // Get the active school ID
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return redirect()->back()->withErrors(['error' => 'No school is currently selected.']);
            }

            // Set school context for role operations
            $this->setSchoolContextForRoles($activeSchoolId);

            // Check if all users have teacher or principal role
            $teachers = User::whereIn('id', $request->teacher_ids)->get();
            $nonTeachers = $teachers->filter(function ($user) {
                return !$user->hasRole(['teacher']);
            });

            if ($nonTeachers->count() > 0) {
                $this->clearSchoolContext();
                $nonTeacherNames = $nonTeachers->pluck('name')->implode(', ');
                return redirect()->back()->withErrors(['error' => 'The following users are not teachers or principals: ' . $nonTeacherNames]);
            }

            // Clear school context after role check
            $this->clearSchoolContext();

            // First, remove all existing assignments for this class-subject combination
            ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->delete();

            // Then, create new assignments for all selected teachers
            $assignments = [];
            foreach ($request->teacher_ids as $userId) {
                // Get the teacher record for this user
                $teacher = Teacher::where('user_id', $userId)->first();

                if ($teacher) {
                    $assignments[] = [
                        'class_id' => $request->class_id,
                        'subject_id' => $request->subject_id,
                        'teacher_id' => $teacher->id,
                        'school_id' => $activeSchoolId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            if (!empty($assignments)) {
                ClassSubjectTeacher::insert($assignments);
            }

            Log::info('Teachers assigned to subject', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_ids' => $request->teacher_ids,
                'count' => count($request->teacher_ids)
            ]);

            $teacherCount = count($request->teacher_ids);
            $message = $teacherCount === 1
                ? '1 teacher assigned to subject successfully!'
                : "{$teacherCount} teachers assigned to subject successfully!";

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            // Ensure school context is cleared on error
            $this->clearSchoolContext();

            Log::error('Error assigning teachers to subject', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to assign teachers to subject: ' . $e->getMessage()]);
        }
    }

    public function getAssignmentsApi(Request $request)
    {
        try {
            $classId = $request->query('class_id');
            $subjectId = $request->query('subject_id');

            if ($classId && $subjectId) {
                // Get teachers assigned to specific class-subject combination
                $assignedTeachers = ClassSubjectTeacher::with(['teacher.user'])
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->teacher->user->id,
                            'name' => $assignment->teacher->user->name
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignedTeachers' => $assignedTeachers
                ]);
            } elseif ($classId) {
                // Get subjects assigned to specific class using Eloquent
                $assignedSubjects = ClassSubject::with('subject')
                    ->where('class_id', $classId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                            'code' => $assignment->subject->code
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignedSubjects' => $assignedSubjects
                ]);
            } else {
                // Get all assignments using Eloquent
                $assignments = ClassSubject::with(['class', 'subject'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'class_id' => $assignment->class_id,
                            'subject_id' => $assignment->subject_id,
                            'school_id' => $assignment->school_id,
                            'class_name' => $assignment->class->name,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'created_at' => $assignment->created_at,
                            'updated_at' => $assignment->updated_at
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignments' => $assignments
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignments(Request $request)
    {
        try {
            $classId = $request->query('class_id');
            $subjectId = $request->query('subject_id');

            Log::info('getAssignments called', [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'all_params' => $request->all()
            ]);

            // Get active school ID
            $activeSchoolId = session('active_school_id');

            if ($classId && $subjectId) {
                // Get teachers assigned to specific class-subject combination using Eloquent
                $assignedTeachers = ClassSubjectTeacher::with(['teacher.user'])
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->teacher->user->id,
                            'name' => $assignment->teacher->user->name
                        ];
                    });

                Log::info('Found assigned teachers', ['count' => $assignedTeachers->count()]);

                return response()->json([
                    'success' => true,
                    'assignedTeachers' => $assignedTeachers
                ]);
            } elseif ($classId) {
                // Get subjects assigned to specific class using Eloquent
                $assignedSubjects = ClassSubject::with('subject')
                    ->where('class_id', $classId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject->id,
                            'name' => $assignment->subject->name,
                            'code' => $assignment->subject->code
                        ];
                    });

                Log::info('Found assigned subjects', [
                    'class_id' => $classId,
                    'count' => $assignedSubjects->count(),
                    'subjects' => $assignedSubjects->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'assignedSubjects' => $assignedSubjects
                ]);
            } else {
                // Get all assignments using Eloquent
                $assignments = ClassSubject::with(['class', 'subject'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'class_id' => $assignment->class_id,
                            'subject_id' => $assignment->subject_id,
                            'school_id' => $assignment->school_id,
                            'class_name' => $assignment->class->name,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'created_at' => $assignment->created_at,
                            'updated_at' => $assignment->updated_at
                        ];
                    });

                // Get teacher assignments data using Eloquent
                $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                    ->forSchool($activeSchoolId)
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'teacher_id' => $assignment->teacher_id,
                            'teacher_user_id' => $assignment->teacher->user_id,
                            'teacher_name' => $assignment->teacher->user->name,
                            'teacher_class_id' => $assignment->teacher->class_id,
                            'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                            'class_id' => $assignment->class_id,
                            'class_name' => $assignment->class->name,
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject->name,
                            'subject_code' => $assignment->subject->code,
                            'school_id' => $assignment->school_id
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'assignments' => $assignments,
                    'teacherAssignments' => $teacherAssignments
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAssignment(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Teacher not found']);
            }

            ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->delete();

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Assignment removed successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing assignment', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to remove assignment: ' . $e->getMessage()]);
        }
    }

    public function removeSpecificAssignment(Request $request)
    {
        Log::info('removeSpecificAssignment called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            Log::info('Teacher lookup result', [
                'user_id' => $request->teacher_id,
                'teacher_found' => $teacher ? true : false,
                'teacher_id' => $teacher ? $teacher->id : null
            ]);

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Teacher not found'
                ], 404);
            }

            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            // Check if the assignment exists before deleting
            $existingAssignment = ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->first();

            Log::info('Assignment lookup result', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
                'school_id' => $activeSchoolId,
                'assignment_found' => $existingAssignment ? true : false,
                'assignment_id' => $existingAssignment ? $existingAssignment->id : null
            ]);

            if (!$existingAssignment) {
                return response()->json([
                    'success' => false,
                    'error' => 'Assignment not found'
                ], 404);
            }

            // Delete the specific assignment
            $deleted = ClassSubjectTeacher::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('Specific teacher assignment removed', [
                'teacher_id' => $teacher->id,
                'teacher_user_id' => $request->teacher_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment removed successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing specific assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to remove assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeTeacherAssignment(Request $request)
    {
        Log::info('removeTeacherAssignment called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Get the teacher record from the user_id
            $teacher = Teacher::where('user_id', $request->teacher_id)->first();

            Log::info('Teacher lookup result', [
                'user_id' => $request->teacher_id,
                'teacher_found' => $teacher ? true : false,
                'teacher_id' => $teacher ? $teacher->id : null
            ]);

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'error' => 'Teacher not found'
                ], 404);
            }

            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            // Check existing assignments before deleting
            $existingAssignments = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->get();

            Log::info('Existing assignments found', [
                'teacher_id' => $teacher->id,
                'school_id' => $activeSchoolId,
                'assignments_count' => $existingAssignments->count(),
                'assignments' => $existingAssignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'class_id' => $assignment->class_id,
                        'subject_id' => $assignment->subject_id
                    ];
                })
            ]);

            // Delete all assignments for this teacher in the active school
            $deleted = ClassSubjectTeacher::where('teacher_id', $teacher->id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('All teacher assignments removed', [
                'teacher_id' => $teacher->id,
                'teacher_user_id' => $request->teacher_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => "All assignments removed for teacher successfully!",
                'deleted_count' => $deleted
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing teacher assignments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to remove teacher assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTeacherAssignments(Request $request)
    {
        try {
            // Get active school ID
            $activeSchoolId = session('active_school_id');

            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                ->forSchool($activeSchoolId)
                ->get()
                ->map(function ($assignment) {
                    return [
                        'teacher_id' => $assignment->teacher_id,
                        'teacher_user_id' => $assignment->teacher->user_id,
                        'teacher_name' => $assignment->teacher->user->name,
                        'teacher_class_id' => $assignment->teacher->class_id,
                        'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : null,
                        'class_id' => $assignment->class_id,
                        'class_name' => $assignment->class->name,
                        'subject_id' => $assignment->subject_id,
                        'subject_name' => $assignment->subject->name,
                        'subject_code' => $assignment->subject->code,
                        'school_id' => $assignment->school_id
                    ];
                });

            Log::info('Teacher assignments loaded', [
                'active_school_id' => $activeSchoolId,
                'assignments_count' => $teacherAssignments->count()
            ]);

            return response()->json([
                'success' => true,
                'teacherAssignments' => $teacherAssignments
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching teacher assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch teacher assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function debugTeacherAssignments(Request $request)
    {
        try {
            // Get active school ID
            $activeSchoolId = session('active_school_id');

            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            // Test 1: Get total teacher assignments using Eloquent
            $totalAssignments = ClassSubjectTeacher::count();

            // Test 2: Get school-specific teacher assignments using Eloquent
            $schoolAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                ->forSchool($activeSchoolId)
                ->get();

            // Test 3: Get teachers in the active school using Eloquent
            $teachersInSchool = User::whereHas('teacher', function ($query) use ($activeSchoolId) {
                $query->forSchool($activeSchoolId);
            })->whereHas('roles', function ($query) {
                $query->whereIn('name', ['teacher', 'principal']);
            })->get();

            // Test 4: Get class-subject assignments for the active school using Eloquent
            $classSubjectAssignments = ClassSubject::with('subject')
                ->where('school_id', $activeSchoolId)
                ->get();

            // Test 5: Get actual teacher assignments with relationships
            $teacherAssignments = ClassSubjectTeacher::with(['teacher.user', 'teacher.class', 'class', 'subject'])
                ->forSchool($activeSchoolId)
                ->get();

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'active_school_id' => $activeSchoolId,
                    'total_teacher_assignments' => $totalAssignments,
                    'school_teacher_assignments' => $schoolAssignments,
                    'teachers_in_school' => $teachersInSchool,
                    'class_subject_assignments' => $classSubjectAssignments,
                    'teacher_assignments_with_relations' => $teacherAssignments->count(),
                    'assignments_data' => $teacherAssignments->map(function ($assignment) {
                        return [
                            'id' => $assignment->id,
                            'teacher_id' => $assignment->teacher_id,
                            'teacher_name' => $assignment->teacher->user->name ?? 'N/A',
                            'teacher_class_id' => $assignment->teacher->class_id,
                            'teacher_class_name' => $assignment->teacher->class ? $assignment->teacher->class->name : 'N/A',
                            'class_id' => $assignment->class_id,
                            'class_name' => $assignment->class->name ?? 'N/A',
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject->name ?? 'N/A',
                            'school_id' => $assignment->school_id
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching teacher assignments for debug', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch teacher assignments for debug: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeSubjectFromClass(Request $request)
    {
        Log::info('removeSubjectFromClass called', [
            'request_data' => $request->all(),
            'active_school_id' => session('active_school_id')
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        try {
            // Get active school ID for additional validation
            $activeSchoolId = session('active_school_id');
            if (!$activeSchoolId) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active school selected'
                ], 400);
            }

            // Check if the assignment exists before deleting
            $existingAssignment = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('school_id', $activeSchoolId)
                ->first();

            Log::info('Subject-class assignment lookup result', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'assignment_found' => $existingAssignment ? true : false,
                'assignment_id' => $existingAssignment ? $existingAssignment->id : null
            ]);

            if (!$existingAssignment) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subject-class assignment not found'
                ], 404);
            }

            // Delete the subject-class assignment
            $deleted = ClassSubject::where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('school_id', $activeSchoolId)
                ->delete();

            Log::info('Subject-class assignment removed', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'school_id' => $activeSchoolId,
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subject removed from class successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing subject from class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to remove subject from class: ' . $e->getMessage()
            ], 500);
        }
    }
}

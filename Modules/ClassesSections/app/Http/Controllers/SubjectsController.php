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

class SubjectsController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::query()->orderBy('name')->get();
        $classes = ClassModel::all();

        // Get teachers - handle both school-specific and global teacher roles
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        // Get assignments data
        $assignments = DB::table('class_subject_teacher')
            ->join('classes', 'class_subject_teacher.class_id', '=', 'classes.id')
            ->join('subjects', 'class_subject_teacher.subject_id', '=', 'subjects.id')
            ->join('users', 'class_subject_teacher.teacher_id', '=', 'users.id')
            ->select(
                'class_subject_teacher.*',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'users.name as teacher_name'
            )
            ->get();

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
            'classes' => $classes,
            'teachers' => $teachers,
            'assignments' => $assignments,
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

            $validated['school_id'] = Auth::user()->school_id;

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

            Log::info('Subject deleted successfully', ['subject_id' => $id]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subject deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting subject', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete subject. Please try again.']);
        }
    }

    public function assignToClass(Request $request, $classId)
    {
        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        try {
            $class = ClassModel::findOrFail($classId);
            $class->subjects()->sync($request->subject_ids);

            Log::info('Subjects assigned to class', [
                'class_id' => $classId,
                'subject_ids' => $request->subject_ids
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
            'teacher_id' => 'required|exists:users,id'
        ]);

        try {
            // Check if teacher has teacher role
            $teacher = User::findOrFail($request->teacher_id);
            if (!$teacher->hasRole('teacher')) {
                return redirect()->back()->withErrors(['error' => 'Selected user is not a teacher']);
            }

            // Create or update the assignment
            DB::table('class_subject_teacher')->updateOrInsert(
                [
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                    'teacher_id' => $request->teacher_id
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            Log::info('Subject assigned to teacher', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id
            ]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Subject assigned to teacher successfully!');
        } catch (\Exception $e) {
            Log::error('Error assigning subject to teacher', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to assign subject to teacher: ' . $e->getMessage()]);
        }
    }

    public function getAssignments(Request $request)
    {
        try {
            $classId = $request->query('class_id');
            $teacherId = $request->query('teacher_id');

            $query = DB::table('class_subject_teacher')
                ->join('classes', 'class_subject_teacher.class_id', '=', 'classes.id')
                ->join('subjects', 'class_subject_teacher.subject_id', '=', 'subjects.id')
                ->join('users', 'class_subject_teacher.teacher_id', '=', 'users.id')
                ->select(
                    'class_subject_teacher.*',
                    'classes.name as class_name',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code',
                    'users.name as teacher_name'
                );

            if ($classId) {
                $query->where('class_subject_teacher.class_id', $classId);
            }

            if ($teacherId) {
                $query->where('class_subject_teacher.teacher_id', $teacherId);
            }

            $assignments = $query->get();

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('assignments', $assignments);
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to fetch assignments: ' . $e->getMessage()]);
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
            DB::table('class_subject_teacher')
                ->where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('teacher_id', $request->teacher_id)
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
}

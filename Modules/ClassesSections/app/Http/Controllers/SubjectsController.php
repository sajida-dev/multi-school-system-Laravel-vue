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
        $teachers = User::role('teacher')->get();

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Subject creation request received', [
            'data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        try {
            $subject = Subject::create($validated);
            Log::info('Subject created successfully', ['subject_id' => $subject->id, 'subject_name' => $subject->name]);

            return response()->json(['success' => true, 'subject' => $subject], 201);
        } catch (\Exception $e) {
            Log::error('Error creating subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to create subject: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        try {
            $subject = Subject::findOrFail($id);
            $subject->update($validated);

            return response()->json(['success' => true, 'subject' => $subject]);
        } catch (\Exception $e) {
            Log::error('Error updating subject', [
                'error' => $e->getMessage(),
                'subject_id' => $id
            ]);

            return response()->json(['error' => 'Failed to update subject: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting subject', [
                'error' => $e->getMessage(),
                'subject_id' => $id
            ]);

            return response()->json(['error' => 'Failed to delete subject: ' . $e->getMessage()], 500);
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

            return response()->json(['success' => true, 'message' => 'Subjects assigned to class successfully']);
        } catch (\Exception $e) {
            Log::error('Error assigning subjects to class', [
                'error' => $e->getMessage(),
                'class_id' => $classId
            ]);

            return response()->json(['error' => 'Failed to assign subjects: ' . $e->getMessage()], 500);
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
                return response()->json(['error' => 'Selected user is not a teacher'], 400);
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

            return response()->json(['success' => true, 'message' => 'Subject assigned to teacher successfully']);
        } catch (\Exception $e) {
            Log::error('Error assigning subject to teacher', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to assign subject to teacher: ' . $e->getMessage()], 500);
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

            return response()->json(['success' => true, 'assignments' => $assignments]);
        } catch (\Exception $e) {
            Log::error('Error fetching assignments', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch assignments: ' . $e->getMessage()], 500);
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

            return response()->json(['success' => true, 'message' => 'Assignment removed successfully']);
        } catch (\Exception $e) {
            Log::error('Error removing assignment', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to remove assignment: ' . $e->getMessage()], 500);
        }
    }
}

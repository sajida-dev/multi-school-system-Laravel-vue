<?php

namespace Modules\Students\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\App\Models\School;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get selected school from session
        $selectedSchoolId = session('active_school_id');
        $query = Student::with(['school', 'class'])
            ->where('status', 'admitted');

        // Always filter by selected school if set
        if ($selectedSchoolId) {
            $query->where('school_id', $selectedSchoolId);
        } elseif ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('b_form_number', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('class_shift')) {
            $query->where('class_shift', $request->class_shift);
        }

        $perPage = $request->input('per_page', 12);
        $students = $query->orderBy('name')->paginate($perPage);

        // Fetch all schools
        $schools = School::orderBy('name')->get(['id', 'name']);

        // Fetch classes for selected school using Eloquent
        $classes = collect();
        $schoolIdForClasses = $selectedSchoolId ?: $request->school_id;
        if ($schoolIdForClasses) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('name');
            }])->find($schoolIdForClasses);
            $classes = $school ? $school->classes : collect();
        }

        // Fetch sections for selected class using Eloquent
        $sections = collect();
        if ($request->filled('class_id') && $schoolIdForClasses) {
            $school = School::with(['classes.sections'])->find($schoolIdForClasses);
            if ($school) {
                $class = $school->classes->where('id', $request->class_id)->first();
                $sections = $class && $class->relationLoaded('sections') ? $class->sections->sortBy('name')->values() : collect();
            }
        }

        return Inertia::render('Students/Index', [
            'students' => $students,
            'schools' => $schools,
            'classes' => $classes,
            'sections' => $sections,
            'filters' => $request->only(['search', 'class_id', 'school_id', 'section_id', 'gender', 'class_shift']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Students/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $student = Student::with(['school', 'class'])->findOrFail($id);
        return Inertia::render('Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = Student::with(['school', 'class'])->findOrFail($id);
        $classes = ClassModel::orderBy('name')->get(['id', 'name']);
        $schools = School::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'classes' => $classes,
            'schools' => $schools,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|unique:students,registration_number,' . $id,
            'b_form_number' => 'required|string|unique:students,b_form_number,' . $id,
            'class_id' => 'required|exists:classes,id',
            'school_id' => 'required|exists:schools,id',
            'nationality' => 'required|string',
            'admission_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'class_shift' => 'required|in:Morning,Evening,Other',
            'previous_school' => 'nullable|string',
            'inclusive' => 'required|string',
            'other_inclusive_type' => 'nullable|string',
            'religion' => 'required|string',
            'is_bricklin' => 'boolean',
            'is_orphan' => 'boolean',
            'is_qsc' => 'boolean',
            'father_name' => 'required|string',
            'guardian_name' => 'nullable|string',
            'father_cnic' => 'required|string',
            'mother_cnic' => 'nullable|string',
            'father_profession' => 'required|string',
            'no_of_children' => 'nullable|integer',
            'job_type' => 'nullable|string',
            'father_education' => 'required|string',
            'mother_education' => 'required|string',
            'mother_profession' => 'required|string',
            'father_income' => 'required|string',
            'mother_income' => 'nullable|string',
            'household_income' => 'required|string',
            'permanent_address' => 'required|string',
            'phone_no' => 'nullable|string',
            'mobile_no' => 'required|string',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}

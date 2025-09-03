<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamType;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;
use Modules\Teachers\Models\Teacher;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $role = $user->roles[0]->name;
        $schoolId = session('active_school_id');

        $exams = Exam::with('examType', 'class', 'section', 'school')->get();
        $examTypes = ExamType::all();

        if ($role === 'superadmin') {
            // Superadmin: get all classes for the selected school
            $classes = ClassModel::forSchool($schoolId)
                ->select('id', 'name')
                ->get()
                ->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                    ];
                })
                ->values();
        } else if ($role === 'teacher') {
            // Teacher: get the class assigned to them via the teachers table
            $teacher = Teacher::where('user_id', $user->id)
                ->where('school_id', $schoolId)
                ->first();

            $classes = [];

            if ($teacher && $teacher->class_id) {
                $class = ClassModel::find($teacher->class_id);
                if ($class) {
                    $classes[] = [
                        'id' => $class->id,
                        'name' => $class->name,
                    ];
                }
            }
        } else {
            // Other roles - optional, return empty or handle accordingly
            $classes = collect();
        }

        return Inertia::render('Exams/ExamsIndex', [
            'examTypes' => $examTypes,
            'exams' => $exams,
            'classes' => $classes,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Exams/Create', [
            'examTypes' => ExamType::all(),
            'schools'   => School::all(),
            'classes'   => ClassModel::classSchools()->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string',
            'exam_type_id'  => 'required|exists:exam_types,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'academic_year' => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'instructions'  => 'nullable|string',
        ]);
        $data['school_id'] = session('active_school_id');

        try {
            DB::transaction(function () use ($data) {
                Exam::create($data);
            });
            return redirect()->route('exams.index')->with('success', 'Exam created.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('resultspromotions::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam)
    {
        return Inertia::render('Exams/Edit', [
            'exam'      => $exam,
            'examTypes' => ExamType::all(),
            'schools'   => School::all(),
            'classes'   => ClassModel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {

        $data = $request->validate([
            'title'         => 'required|string',
            'exam_type_id'  => 'required|exists:exam_types,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'academic_year' => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'instructions'  => 'nullable|string',
        ]);
        $data['school_id'] = session('active_school_id');
        try {
            DB::transaction(function () use ($exam, $data) {
                $exam->update($data);
            });
            return redirect()->route('exams.index')->with('success', 'Updated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        try {
            DB::transaction(function () use ($exam) {
                $exam->delete();
            });
            return redirect()->route('exams.index')->with('success', 'Deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}

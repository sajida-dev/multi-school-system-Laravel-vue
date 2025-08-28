<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\app\Models\ExamType;
use Modules\Schools\App\Models\School;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = Exam::with('examType', 'school')->get();
        return Inertia::render('Exams/Index', compact('exams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Exams/Create', [
            'examTypes' => ExamType::all(),
            'schools'   => School::all(),
            'classes'   => ClassModel::all(),
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
            'school_id'     => 'required|exists:schools,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'academic_year' => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'status'        => 'required|in:scheduled,in_progress,completed,cancelled',
            'instructions'  => 'nullable|string',
        ]);

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
            'school_id'     => 'required|exists:schools,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'academic_year' => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'status'        => 'required|in:scheduled,in_progress,completed,cancelled',
            'instructions'  => 'nullable|string',
        ]);

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

<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\app\Models\Exam;
use Modules\ResultsPromotions\Models\ExamPaper;

class ExamPaperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd(ExamPaper::with('exam', 'paper')->get());
        return Inertia::render('Exams/ExamPapersIndex', [
            'examPapers' => ExamPaper::with('exam', 'paper')->get()->map(function ($ep) {
                return [
                    'id' => $ep->id,
                    'exam_date' => $ep->exam_date,
                    'start_time' => $ep->start_time,
                    'end_time' => $ep->end_time,
                    'total_marks' => $ep->total_marks,
                    'passing_marks' => $ep->passing_marks,
                    'exam' => [
                        'id' => $ep->exam->id,
                        'title' => $ep->exam->title,
                    ],
                    'paper' => [
                        'id' => $ep->paper->id,
                        'title' => $ep->paper->title,
                    ],
                ];
            }),
            'exams' => Exam::select('id', 'title')->get(),
            'papers' => Paper::select('id', 'title')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('resultspromotions::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'paper_id' => 'required|exists:papers,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i A',
            'end_time' => 'required|date_format:H:i A|after_or_equal:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
        ]);

        // ✅ Create the exam paper
        $examPaper = ExamPaper::create($validated);

        // ✅ Return success response
        return redirect()
            ->route('exam-papers.index')
            ->with('success', 'Exam paper created successfully.');
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
    public function edit($id)
    {
        return view('resultspromotions::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

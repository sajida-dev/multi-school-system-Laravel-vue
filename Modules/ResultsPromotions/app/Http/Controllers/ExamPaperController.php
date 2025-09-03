<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\App\Models\Exam;
use Modules\ResultsPromotions\Models\ExamPaper;
use Carbon\Carbon;
use Exception;

class ExamPaperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $examPaper = ExamPaper::with('exam', 'paper')->get()->map(function ($ep) {
            $ep->start_time = Carbon::parse($ep->start_time)->format('H:i');
            $ep->end_time = Carbon::parse($ep->end_time)->format('H:i');
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
        });

        return Inertia::render('Exams/ExamPapersIndex', [
            'examPapers' => $examPaper,
            'exams' => Exam::select('id', 'title')->get(),
            'papers' => Paper::select('id', 'title')->get(),
        ]);
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
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
        ]);


        DB::beginTransaction();

        try {
            // Convert times to 24-hour format for DB storage
            $validated['start_time'] = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i');
            $validated['end_time'] = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i');

            ExamPaper::create($validated);

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper created successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to create exam paper: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $examPaper = ExamPaper::findOrFail($id);

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'paper_id' => 'required|exists:papers,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after_or_equal:start_time',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0|lte:total_marks',
        ]);

        DB::beginTransaction();

        try {
            // Convert times to 24-hour format
            $validated['start_time'] = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i');
            $validated['end_time'] = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i');

            $examPaper->update($validated);

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to update exam paper: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $examPaper = ExamPaper::findOrFail($id);

        DB::beginTransaction();

        try {
            $examPaper->delete();

            DB::commit();

            return redirect()
                ->route('exam-papers.index')
                ->with('success', 'Exam paper deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('exam-papers.index')
                ->withErrors(['error' => 'Failed to delete exam paper: ' . $e->getMessage()]);
        }
    }
}

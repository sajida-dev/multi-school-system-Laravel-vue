<?php

namespace Modules\ResultsPromotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\app\Models\Section;
use Modules\ResultsPromotions\app\Models\ExamResult;
use Modules\ResultsPromotions\app\Models\ExamType;
use Modules\ResultsPromotions\Models\ExamPaper;
use Modules\Schools\App\Models\School;

class ExamResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $schoolId = session('active_school_id');

    //     // Get classes for the current school
    //     $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
    //         $q->where('schools.id', $schoolId);
    //     })->orderBy('name')->get(['id', 'name']);

    //     // Get sections for the current school
    //     $sections = Section::whereIn('id', function ($query) use ($schoolId) {
    //         $query->select('class_school_sections.section_id')
    //             ->from('class_school_sections')
    //             ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
    //             ->where('class_schools.school_id', $schoolId);
    //     })->orderBy('name')->get(['id', 'name']);

    //     // Get selected filters
    //     $selectedClass = $request->input('class_id');
    //     $selectedSection = $request->input('section_id');
    //     $selectedTerm = $request->input('term', '1st_term');

    //     $results = collect();

    //     if ($selectedClass) {
    //         // Get students with their results
    //         $students = Student::whereHas('class', function ($q) use ($selectedClass) {
    //             $q->where('classes.id', $selectedClass);
    //         })
    //             ->when($selectedSection, function ($q) use ($selectedSection) {
    //                 $q->whereHas('section', function ($sq) use ($selectedSection) {
    //                     $sq->where('sections.id', $selectedSection);
    //                 });
    //             })
    //             ->where('school_id', $schoolId)
    //             ->with(['class', 'section', 'results' => function ($q) use ($selectedTerm) {
    //                 $q->where('term', $selectedTerm);
    //             }])
    //             ->orderBy('registration_number')
    //             ->get();

    //         foreach ($students as $student) {
    //             $results->push([
    //                 'student' => $student,
    //                 'results' => $student->results,
    //                 'total_marks' => $student->results->sum('obtained_marks'),
    //                 'total_possible_marks' => $student->results->sum('total_marks'),
    //                 'percentage' => $student->results->count() > 0 ?
    //                     round(($student->results->sum('obtained_marks') / $student->results->sum('total_marks')) * 100, 2) : 0,
    //             ]);
    //         }
    //     }

    //     return Inertia::render('ExamResults/Index', [
    //         'classes' => $classes,
    //         'sections' => $sections,
    //         'results' => $results,
    //         'selectedClass' => $selectedClass,
    //         'selectedSection' => $selectedSection,
    //         'selectedTerm' => $selectedTerm,
    //         'terms' => [
    //             '1st_term' => '1st Term',
    //             '2nd_term' => '2nd Term',
    //             '3rd_term' => '3rd Term',
    //             'final' => 'Final Term'
    //         ],
    //     ]);
    // }

    public function index(Request $request)
    {
        $schoolId = session('active_school_id');

        // Fetch dropdown data
        $classes = ClassModel::whereHas('schools', fn($q) => $q->where('schools.id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $sections = Section::whereIn('id', function ($query) use ($schoolId) {
            $query->select('class_school_sections.section_id')
                ->from('class_school_sections')
                ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                ->where('class_schools.school_id', $schoolId);
        })->orderBy('name')->get(['id', 'name']);

        // Filters
        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedTerm = $request->input('term', '1st_term');

        $results = collect();

        if ($selectedClass) {
            $students = Student::whereHas('class', fn($q) => $q->where('classes.id', $selectedClass))
                ->when($selectedSection, fn($q) => $q->whereHas('section', fn($sq) => $sq->where('sections.id', $selectedSection)))
                ->where('school_id', $schoolId)
                ->admitted()
                ->with([
                    'class',
                    'section',
                    'results.examPaper.exam.examType'
                ])
                ->orderBy('registration_number')
                ->get();

            foreach ($students as $student) {
                // Filter results by term via examType name
                $termResults = $student->results->filter(function ($result) use ($selectedTerm) {
                    return optional($result->examPaper->exam->examType)->code === $selectedTerm;
                });

                // dd($termResults);

                $results->push([
                    'student' => $student,
                    'results' => $termResults->values(),
                    'total_marks' => $termResults->sum('obtained_marks'),
                    'total_possible_marks' => $termResults->sum('total_marks'),
                    'percentage' => $termResults->count() > 0
                        ? round(($termResults->sum('obtained_marks') / $termResults->sum('total_marks')) * 100, 2)
                        : 0,
                ]);
            }
        }
        $terms = ExamType::pluck('name', 'code');

        return Inertia::render('ExamResults/Index', [
            'classes' => $classes,
            'sections' => $sections,
            'results' => $results,
            'selectedClass' => $selectedClass,
            'selectedSection' => $selectedSection,
            'selectedTerm' => $selectedTerm,
            'terms' => $terms,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->input('school_id') ?? session('active_school_id');
        $classId = $request->input('class_id');
        $examPaperId = $request->input('exam_paper_id');

        $schools = School::select('id', 'name')->get();

        $classes = $schoolId
            ? ClassModel::forSchool($schoolId)->select('id', 'name')->get()
            : [];

        $students = ($classId)
            ? Student::where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->admitted()
            ->select('id', 'name', 'registration_number')
            ->get()
            : [];

        $examPapers = ($classId)
            ? ExamPaper::whereHas('exam', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->with(['exam', 'paper']) // To access exam.name and paper.name later
            ->get()
            : [];

        return Inertia::render('ExamResults/Create', [
            'schools' => $schools,
            'classes' => $classes,
            'students' => $students,
            'examPapers' => $examPapers,
            'selectedSchoolId' => $schoolId,
            'selectedClassId' => $classId,
            'selectedExamPaperId' => $examPaperId,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'exam_paper_id' => 'required|exists:exam_paper,id',
            'results' => 'required|array|min:1',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.obtained_marks' => 'required|numeric|min:0',
            'results.*.total_marks' => 'required|numeric|min:1',
            'results.*.status' => 'required|in:pass,fail,absent',
            'results.*.promotion_status' => 'required|in:promoted,failed,pending',
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        foreach ($request->results as $result) {
            ExamResult::updateOrCreate(
                [
                    'exam_paper_id' => $request->exam_paper_id,
                    'student_id' => $result['student_id'],
                ],
                [
                    'obtained_marks' => $result['obtained_marks'],
                    'total_marks' => $result['total_marks'],
                    'percentage' => ($result['total_marks'] > 0)
                        ? ($result['obtained_marks'] / $result['total_marks']) * 100
                        : null,
                    'status' => $result['status'],
                    'promotion_status' => $result['promotion_status'],
                    'remarks' => $result['remarks'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('exam-results.index')->with('success', 'Exam results saved successfully.');
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
    public function edit($examPaperId)
    {
        $examPaper = ExamPaper::with('class', 'exam')->findOrFail($examPaperId);

        $schoolId = session('active_school_id');
        $classId = $examPaper->class_id;

        $students = Student::where('class_id', $classId)
            ->where('school_id', $schoolId)
            ->select('id', 'name', 'registration_number')
            ->get();

        $existingResults = ExamResult::where('exam_paper_id', $examPaperId)
            ->get()
            ->keyBy('student_id');

        $results = $students->map(function ($student) use ($existingResults) {
            $result = $existingResults->get($student->id);

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'registration_number' => $student->registration_number,
                'obtained_marks' => $result->obtained_marks ?? '',
                'total_marks' => $result->total_marks ?? '',
                'status' => $result->status ?? 'pass',
                'promotion_status' => $result->promotion_status ?? 'pending',
                'remarks' => $result->remarks ?? '',
            ];
        });

        return Inertia::render('ExamResults/Edit', [
            'examPaper' => [
                'id' => $examPaper->id,
                'name' => $examPaper->name,
            ],
            'class' => [
                'id' => $examPaper->class->id,
                'name' => $examPaper->class->name,
            ],
            'results' => $results,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $examPaperId)
    {
        $request->validate([
            'results' => 'required|array|min:1',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.obtained_marks' => 'required|numeric|min:0',
            'results.*.total_marks' => 'required|numeric|min:1',
            'results.*.status' => 'required|in:pass,fail,absent',
            'results.*.promotion_status' => 'required|in:promoted,failed,pending',
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        foreach ($request->results as $result) {
            ExamResult::updateOrCreate(
                [
                    'exam_paper_id' => $examPaperId,
                    'student_id' => $result['student_id'],
                ],
                [
                    'obtained_marks' => $result['obtained_marks'],
                    'total_marks' => $result['total_marks'],
                    'percentage' => ($result['total_marks'] > 0)
                        ? ($result['obtained_marks'] / $result['total_marks']) * 100
                        : null,
                    'status' => $result['status'],
                    'promotion_status' => $result['promotion_status'],
                    'remarks' => $result['remarks'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('exam-results.index')->with('success', 'Exam results updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

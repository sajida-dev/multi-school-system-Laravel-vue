<?php

namespace Modules\PapersQuestions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\PapersQuestions\App\Models\Question;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PapersQuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');
        $query = Paper::query()->with(['class', 'section', 'teacher']);

        // Filter by selected school
        if ($schoolId) {
            $query->whereHas('class.schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            });
        }

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('published')) {
            $query->where('published', $request->published);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 12);
        $papers = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        // Get classes, sections, and teachers for filters
        $classes = collect();
        $sections = collect();
        $teachers = collect();

        if ($schoolId) {
            $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get sections that are associated with classes in this school through pivot tables
            $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                $query->select('class_school_sections.section_id')
                    ->from('class_school_sections')
                    ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                    ->where('class_schools.school_id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get teachers with their names from users table
            $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.school_id', $schoolId)
                ->orderBy('users.name')
                ->get(['teachers.id', 'users.name']);
        }

        return Inertia::render('PapersQuestions/Index', [
            'papers' => $papers,
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
            'filters' => $request->only(['class_id', 'section_id', 'teacher_id', 'published', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schoolId = session('active_school_id');

        $classes = collect();
        $sections = collect();
        $teachers = collect();

        if ($schoolId) {
            $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get sections that are associated with classes in this school through pivot tables
            $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                $query->select('class_school_sections.section_id')
                    ->from('class_school_sections')
                    ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                    ->where('class_schools.school_id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get teachers with their names from users table
            $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.school_id', $schoolId)
                ->orderBy('users.name')
                ->get(['teachers.id', 'users.name']);
        }

        return Inertia::render('PapersQuestions/Create', [
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'published' => 'boolean',
            'total_marks' => 'nullable|integer|min:0',
            'time_duration' => 'nullable|integer|min:1',
            'subject_name' => 'nullable|string|max:255',
            'subject_code' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer,long_answer,essay,numerical',
            'questions.*.section' => 'required|in:objective,short_questions,long_questions,essay',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.question_number' => 'nullable|integer|min:1',
            'questions.*.options' => 'nullable|array',
            'questions.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $paper = Paper::create([
                'title' => $request->title,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id,
                'published' => $request->published ?? false,
                'total_marks' => $request->total_marks,
                'time_duration' => $request->time_duration ?? 120,
                'subject_name' => $request->subject_name,
                'subject_code' => $request->subject_code,
                'instructions' => $request->instructions,
            ]);

            // Create questions
            foreach ($request->questions as $questionData) {
                Question::create([
                    'paper_id' => $paper->id,
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'section' => $questionData['section'],
                    'marks' => $questionData['marks'],
                    'question_number' => $questionData['question_number'] ?? null,
                    'options' => $questionData['options'] ?? null,
                    'answer' => $questionData['answer'] ?? null,
                ]);
            }

            DB::commit();

            // Return redirect to show the created paper
            return redirect()->route('papersquestions.show', $paper->id)
                ->with('success', 'Paper created successfully with ' . count($request->questions) . ' questions.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create paper. Please try again.'])->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $paper = Paper::with(['class', 'section', 'teacher', 'questions'])->findOrFail($id);

        return Inertia::render('PapersQuestions/Show', [
            'paper' => $paper,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $paper = Paper::with(['questions'])->findOrFail($id);

        $schoolId = session('active_school_id');

        $classes = collect();
        $sections = collect();
        $teachers = collect();

        if ($schoolId) {
            $classes = ClassModel::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get sections that are associated with classes in this school through pivot tables
            $sections = Section::whereIn('id', function ($query) use ($schoolId) {
                $query->select('class_school_sections.section_id')
                    ->from('class_school_sections')
                    ->join('class_schools', 'class_school_sections.class_school_id', '=', 'class_schools.id')
                    ->where('class_schools.school_id', $schoolId);
            })->orderBy('name')->get(['id', 'name']);

            // Get teachers with their names from users table
            $teachers = Teacher::join('users', 'teachers.user_id', '=', 'users.id')
                ->where('teachers.school_id', $schoolId)
                ->orderBy('users.name')
                ->get(['teachers.id', 'users.name']);
        }

        return Inertia::render('PapersQuestions/Edit', [
            'paper' => $paper,
            'classes' => $classes,
            'sections' => $sections,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paper = Paper::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'published' => 'boolean',
            'total_marks' => 'nullable|integer|min:0',
            'time_duration' => 'nullable|integer|min:1',
            'subject_name' => 'nullable|string|max:255',
            'subject_code' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:multiple_choice,true_false,short_answer,long_answer,essay,numerical',
            'questions.*.section' => 'required|in:objective,short_questions,long_questions,essay',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.question_number' => 'nullable|integer|min:1',
            'questions.*.clo' => 'nullable|string|max:50',
            'questions.*.options' => 'nullable|array',
            'questions.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $paper->update([
                'title' => $request->title,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'teacher_id' => $request->teacher_id,
                'published' => $request->published ?? false,
                'total_marks' => $request->total_marks,
                'time_duration' => $request->time_duration ?? 120,
                'course_name' => $request->course_name,
                'course_code' => $request->course_code,
                'program' => $request->program,
                'semester' => $request->semester,
                'session' => $request->session,
                'exam_date' => $request->exam_date,
                'instructions' => $request->instructions,
            ]);

            // Delete existing questions and create new ones
            $paper->questions()->delete();

            foreach ($request->questions as $questionData) {
                Question::create([
                    'paper_id' => $paper->id,
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'section' => $questionData['section'],
                    'marks' => $questionData['marks'],
                    'question_number' => $questionData['question_number'] ?? null,
                    'clo' => $questionData['clo'] ?? null,
                    'options' => $questionData['options'] ?? null,
                    'answer' => $questionData['answer'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('papersquestions.index')
                ->with('success', 'Paper updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('papersquestions.index')->withErrors(['error' => 'Failed to update paper. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paper = Paper::findOrFail($id);

        // Only allow deletion if paper is not published
        if ($paper->published) {
            return back()->withErrors(['error' => 'Cannot delete a published paper.']);
        }

        $paper->delete();

        return redirect()->route('papersquestions.index')
            ->with('success', 'Paper deleted successfully.');
    }
}

<?php

namespace Modules\ResultsPromotions\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ResultsPromotions\app\Models\ExamType;

class ExamTypeController extends Controller
{
    public function index()
    {
        $examTypes = ExamType::all();

        return Inertia::render('Exams/ExamTypesIndex', [
            'examTypes' => $examTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:exam_types,code',
            'is_final_term' => 'boolean',
        ]);

        ExamType::create($request->only('name', 'code', 'is_final_term'));

        return redirect()->back()->with('success', 'Exam type created.');
    }

    public function update(Request $request, ExamType $examType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:exam_types,code,' . $examType->id,
            'is_final_term' => 'boolean',
        ]);

        $examType->update($request->only('name', 'code', 'is_final_term'));

        return redirect()->back()->with('success', 'Exam type updated.');
    }

    public function destroy(ExamType $examType)
    {
        $examType->delete();
        return redirect()->back()->with('success', 'Exam type deleted.');
    }
}

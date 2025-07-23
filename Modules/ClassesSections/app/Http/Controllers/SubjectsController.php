<?php

namespace Modules\ClassesSections\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ClassesSections\App\Models\Subject;
use Modules\ClassesSections\App\Models\ClassModel;
use Inertia\Inertia;

class SubjectsController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::query()->orderBy('name')->get();
        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        Subject::create($validated);
        return redirect()->back()->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $subject = Subject::findOrFail($id);
        $subject->update($validated);
        return redirect()->back()->with('success', 'Subject updated successfully.');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully.');
    }

    public function assignToClass(Request $request, $classId)
    {
        $class = ClassModel::findOrFail($classId);
        $subjectIds = $request->input('subject_ids', []);
        $class->subjects()->sync($subjectIds);
        return redirect()->back()->with('success', 'Subjects assigned to class successfully.');
    }
}

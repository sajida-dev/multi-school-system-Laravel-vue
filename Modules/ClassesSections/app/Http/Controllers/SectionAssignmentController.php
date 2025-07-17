<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\App\Models\ClassSchool;
use Modules\ClassesSections\app\Models\Section;

class SectionAssignmentController extends Controller
{
    public function assign(Request $request, ClassSchool $class)
    {
        $request->validate(['section_ids' => 'required|array']);
        $class->sections()->syncWithoutDetaching($request->section_ids);
        return back()->with('success', 'Sections assigned!');
    }

    public function unassign(ClassSchool $class, Section $section)
    {
        $class->sections()->detach($section->id);
        return back()->with('success', 'Section unassigned!');
    }

    public function index(Request $request)
    {
        $schoolId = $request->query('school_id');
        $classes = [];
        if ($schoolId) {
            $classes = ClassSchool::whereHas('schools', function ($q) use ($schoolId) {
                $q->where('schools.id', $schoolId);
            })->with('sections')->get();
        }
        $sections = Section::all();
        return response()->json([
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }
}

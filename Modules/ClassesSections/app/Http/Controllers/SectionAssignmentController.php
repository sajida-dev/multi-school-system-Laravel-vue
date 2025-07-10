<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\app\Models\SchoolClass;
use Modules\ClassesSections\app\Models\Section;

class SectionAssignmentController extends Controller
{
    public function assign(Request $request, SchoolClass $class)
    {
        $request->validate(['section_ids' => 'required|array']);
        $class->sections()->syncWithoutDetaching($request->section_ids);
        return back()->with('success', 'Sections assigned!');
    }

    public function unassign(SchoolClass $class, Section $section)
    {
        $class->sections()->detach($section->id);
        return back()->with('success', 'Section unassigned!');
    }
}

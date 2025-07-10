<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Schools\app\Models\School;
use Modules\ClassesSections\app\Models\SchoolClass;

class ClassAssignmentController extends Controller
{
    public function assign(Request $request, School $school)
    {
        $request->validate(['class_ids' => 'required|array']);
        $school->classes()->syncWithoutDetaching($request->class_ids);
        return back()->with('success', 'Classes assigned!');
    }

    public function unassign(School $school, SchoolClass $class)
    {
        $school->classes()->detach($class->id);
        return back()->with('success', 'Class unassigned!');
    }
}

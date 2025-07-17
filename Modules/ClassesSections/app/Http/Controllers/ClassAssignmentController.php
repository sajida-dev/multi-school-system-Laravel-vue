<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\App\Models\ClassSchool;
use Modules\Schools\app\Models\School;

class ClassAssignmentController extends Controller
{
    public function assign(Request $request, School $school)
    {
        $request->validate(['class_ids' => 'required|array']);
        $school->classes()->syncWithoutDetaching($request->class_ids);
        return back()->with('success', 'Classes assigned!');
    }

    public function unassign(School $school, ClassSchool $class)
    {
        $school->classes()->detach($class->id);
        return back()->with('success', 'Class unassigned!');
    }

    public function index()
    {
        $schools = School::with('classes')->get();
        $classes = ClassSchool::all();
        return response()->json([
            'schools' => $schools,
            'classes' => $classes,
        ]);
    }
}

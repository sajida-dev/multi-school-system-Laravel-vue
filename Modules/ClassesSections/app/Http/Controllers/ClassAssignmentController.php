<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\app\Models\School;

class ClassAssignmentController extends Controller
{
    public function assign(Request $request, School $school)
    {
        $request->validate(['class_ids' => 'required|array']);
        // Sync classes for the school using the class_schools pivot table
        $school->classes()->sync($request->class_ids);
        return back()->with('success', 'Classes assigned!');
    }

    public function unassign(School $school, ClassModel $class)
    {
        $school->classes()->detach($class->id);
        return back()->with('success', 'Class unassigned!');
    }

    public function index(Request $request)
    {
        $schools = School::with('classes')->get();
        $classes = ClassModel::all();
        return response()->json([
            'schools' => $schools,
            'classes' => $classes,
        ]);
    }
}

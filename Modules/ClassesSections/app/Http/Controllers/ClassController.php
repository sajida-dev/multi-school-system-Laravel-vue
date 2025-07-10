<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\app\Models\SchoolClass;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::all();
        return Inertia::render('Classes/Index', [
            'classes' => $classes,
        ]);
    }

    public function create()
    {
        return Inertia::render('Classes/Create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        SchoolClass::create($request->only('name'));
        return redirect()->route('classes.index')->with('success', 'Class created!');
    }

    public function edit(SchoolClass $class)
    {
        return Inertia::render('Classes/Edit', [
            'class' => $class,
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $class->update($request->only('name'));
        return redirect()->route('classes.index')->with('success', 'Class updated!');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted!');
    }
}

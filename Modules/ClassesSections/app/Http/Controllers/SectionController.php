<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\app\Models\Section;
use Inertia\Inertia;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::all();
        return Inertia::render('Sections/Index', [
            'sections' => $sections,
        ]);
    }

    public function create()
    {
        return Inertia::render('Sections/Create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Section::create($request->only('name'));
        return redirect()->route('sections.index')->with('success', 'Section created!');
    }

    public function edit(Section $section)
    {
        return Inertia::render('Sections/Edit', [
            'section' => $section,
        ]);
    }

    public function update(Request $request, Section $section)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $section->update($request->only('name'));
        return redirect()->route('sections.index')->with('success', 'Section updated!');
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return redirect()->route('sections.index')->with('success', 'Section deleted!');
    }
}

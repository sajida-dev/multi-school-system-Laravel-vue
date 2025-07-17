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
        $section = Section::create($request->only('name'));
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Section created!',
                    'initialTab' => 'sections'
                ]);
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'section' => $section], 201);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Section created!',
                'initialTab' => 'sections'
            ]);
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
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Section updated!',
                    'initialTab' => 'sections'
                ]);
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'section' => $section]);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Section updated!',
                'initialTab' => 'sections'
            ]);
    }

    public function destroy(Section $section)
    {
        $section->delete();
        if (request()->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Section deleted!',
                    'initialTab' => 'sections'
                ]);
        }
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Section deleted!',
                'initialTab' => 'sections'
            ]);
    }
}

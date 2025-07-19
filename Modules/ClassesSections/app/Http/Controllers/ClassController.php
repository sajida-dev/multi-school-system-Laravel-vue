<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ClassesSections\app\Models\ClassModel;
use Inertia\Inertia;
use Modules\ClassesSections\app\Models\Section;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::all();
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
        $class = ClassModel::create($request->only('name'));

        // Auto-assign Section A or multiple sections if requested
        if ($request->boolean('auto_assign_sections')) {
            $sectionNames = $request->input('section_names', ['A']);
            $sectionIds = [];
            foreach ($sectionNames as $sectionName) {
                $section = Section::firstOrCreate([
                    'name' => $sectionName
                ]);
                $sectionIds[] = $section->id;
            }
            $class->sections()->syncWithoutDetaching($sectionIds);
        }

        if ($request->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Class created!',
                    'initialTab' => 'classes'
                ]);
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'class' => $class], 201);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Class created!',
                'initialTab' => 'classes'
            ]);
    }

    public function edit(ClassModel $class)
    {
        return Inertia::render('Classes/Edit', [
            'class' => $class,
        ]);
    }

    public function update(Request $request, ClassModel $class)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $class->update($request->only('name'));
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Class updated!',
                    'initialTab' => 'classes'
                ]);
        }
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'class' => $class]);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Class updated!',
                'initialTab' => 'classes'
            ]);
    }

    public function destroy(ClassModel $class)
    {
        $class->delete();
        if (request()->hasHeader('X-Inertia')) {
            return redirect()->route('classes-sections.manage')
                ->with([
                    'success' => 'Class deleted!',
                    'initialTab' => 'classes'
                ]);
        }
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('classes-sections.manage')
            ->with([
                'success' => 'Class deleted!',
                'initialTab' => 'classes'
            ]);
    }
}

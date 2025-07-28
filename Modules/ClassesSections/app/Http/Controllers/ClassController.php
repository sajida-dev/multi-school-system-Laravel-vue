<?php

namespace Modules\ClassesSections\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        Log::info('Class creation request received', [
            'data' => $request->all(),
            'user_id' => Auth::id(),
            'headers' => $request->headers->all()
        ]);

        $request->validate(['name' => 'required|string|max:255']);

        try {
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

            Log::info('Class created successfully', ['class_id' => $class->id, 'class_name' => $class->name]);

            // Return redirect for Inertia instead of JSON
            return redirect()->back()->with('success', 'Class created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create class: ' . $e->getMessage()]);
        }
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

        // Return redirect for Inertia instead of JSON
        return redirect()->back()->with('success', 'Class updated successfully!');
    }

    public function destroy(ClassModel $class)
    {
        $class->delete();

        // Return redirect for Inertia instead of JSON
        return redirect()->back()->with('success', 'Class deleted successfully!');
    }
}

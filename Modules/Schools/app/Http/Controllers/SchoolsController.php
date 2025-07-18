<?php

namespace Modules\Schools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Schools\App\Models\School;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Schools\App\Events\SchoolCreated;
use Modules\Schools\App\Imports\SchoolsImport;

class SchoolsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = School::query();
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%")
                ->orWhere('contact', 'like', "%$search%")
            ;
        }
        $schools = $query->orderByDesc('id')->paginate($request->input('rowsPerPage', 10));
        $schools->getCollection()->transform(function ($school) {
            return [
                'id' => (string) $school->id,
                'name' => (string) $school->name,
                'address' => (string) ($school->address ?? ''),
                'contact' => (string) ($school->contact ?? ''),
            ];
        });
        return Inertia::render('schools/Index', [
            'schools' => $schools,
            'toast' => session('toast'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('schools/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
        ]);
        $school = new School($validated);
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('school_logos', 'public');
            $school->logo = $logoPath;
        }
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $mainImagePath = $request->file('main_image')->store('school_main_images', 'public');
            $school->main_image = $mainImagePath;
        }
        $school->save();
        // Broadcast the event
        // event(new \Modules\Schools\App\Events\SchoolCreated($school));
        return redirect()->route('schools.index')->with([
            'success' => 'School created successfully.',
            'school' => $school,
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $school = School::findOrFail($id);
        return Inertia::render('schools/Show', [
            'school' => $school,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $school = School::findOrFail($id);
        return Inertia::render('schools/Edit', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
        ]);
        $school = School::findOrFail($id);
        $school->update($validated);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('school_logos', 'public');
            $school->logo = $logoPath;
            $school->save();
        }
        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $mainImagePath = $request->file('main_image')->store('school_main_images', 'public');
            $school->main_image = $mainImagePath;
            $school->save();
        }

        // Broadcast the event
        // event(new \Modules\Schools\App\Events\SchoolUpdated($school));

        // If AJAX or expects JSON, return updated school
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['school' => $school, 'success' => 'School updated successfully.']);
        }

        return redirect()->route('schools.index')->with(
            'success',
            'School updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();
        // Broadcast the event
        // event(new \Modules\Schools\App\Events\SchoolDeleted($school->id));
        return redirect()->route('schools.index')->with('success', 'School deleted successfully.');
    }

    /**
     * Import schools from an uploaded Excel or CSV file.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);
        Excel::import(new SchoolsImport, $request->file('file'));
        return response()->json(['message' => 'Import successful!']);
    }

    /**
     * Export all schools as a PDF file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportPdf()
    {
        $schools = \Modules\Schools\App\Models\School::all();
        $pdf = Pdf::loadView('schools.pdf', compact('schools'));
        return $pdf->download('schools.pdf');
    }

    /**
     * Return all schools as JSON (for super admin school switcher)
     */
    public function listJson()
    {
        $schools = \Modules\Schools\App\Models\School::select('id', 'name')->orderBy('name')->get();
        $classes = \Modules\ClassesSections\App\Models\ClassSchool::select('id', 'name')->get();
        $sections = \Modules\ClassesSections\App\Models\Section::select('id', 'name')->get();
        return response()->json([
            'schools' => $schools,
            'classes' => $classes,
            'sections' => $sections,
        ]);
    }

    /**
     * Return all schools with their linked classes and sections as JSON (for frontend forms)
     */
    public function allWithClassesSections()
    {
        $schools = \Modules\Schools\App\Models\School::with('classes.sections')->get();
        return response()->json([
            'schools' => $schools
        ]);
    }
}

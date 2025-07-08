<?php

namespace Modules\Schools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Schools\App\Models\School;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

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
                ->orWhere('contact', 'like', "%$search%");
        }
        $schools = $query->orderByDesc('id')->paginate($request->input('rowsPerPage', 10));
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
        School::create($validated);
        return Redirect::route('schools.index')->with('toast', [
            'type' => 'success',
            'message' => 'School created successfully.'
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
        return Redirect::route('schools.index')->with('toast', [
            'type' => 'success',
            'message' => 'School updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();
        return Redirect::route('schools.index')->with('toast', [
            'type' => 'success',
            'message' => 'School deleted successfully.'
        ]);
    }
}

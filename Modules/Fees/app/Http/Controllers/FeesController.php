<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Fees\App\Models\Fee;
use Modules\Admissions\App\Models\Student;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\Schools\App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Modules\Fees\App\Http\Requests\StoreFeeRequest;
use App\Http\Requests\Modules\Fees\App\Http\Requests\UpdateFeeRequest;

class FeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = session('active_school_id');
        $query = Fee::query()->with(['student.school', 'student.class']);

        // Filter by selected school
        if ($schoolId) {
            $query->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
        if ($request->filled('amount')) {
            $query->where('amount', $request->amount);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $perPage = $request->input('per_page', 12);
        $fees = $query->orderBy('due_date', 'desc')->paginate($perPage)->withQueryString();

        // Get schools and classes for filters
        $schools = School::orderBy('name')->get(['id', 'name']);
        $classes = collect();
        if ($schoolId) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('name');
            }])->find($schoolId);
            $classes = $school ? $school->classes : collect();
        }

        return Inertia::render('Fees/Index', [
            'fees' => $fees,
            'schools' => $schools,
            'classes' => $classes,
            'filters' => $request->only(['type', 'status', 'search', 'due_date_from', 'due_date_to', 'amount', 'student_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schoolId = $request->input('school_id') ?: session('active_school_id');

        // Get schools and classes for the form
        $schools = School::orderBy('name')->get(['id', 'name']);
        $classes = collect();
        $students = collect();

        // If a school is selected, get its classes
        if ($schoolId) {
            $school = School::with(['classes' => function ($q) {
                $q->orderBy('name');
            }])->find($schoolId);
            $classes = $school ? $school->classes : collect();

            // If a class is also selected, get its students
            if ($request->filled('class_id')) {
                $students = Student::where('school_id', $schoolId)
                    ->where('class_id', $request->class_id)
                    ->where('status', 'admitted')
                    ->select('id', 'name', 'registration_number')
                    ->orderBy('name')
                    ->get();
            }
        }

        return Inertia::render('Fees/Create', [
            'schools' => $schools,
            'classes' => $classes,
            'students' => $students,
            'selectedSchoolId' => $schoolId,
            'selectedClassId' => $request->input('class_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Get all students in the selected class and school
                $students = Student::where('school_id', $validated['school_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('status', 'admitted')
                    ->get();

                if ($students->isEmpty()) {
                    return back()->withErrors([
                        'class_id' => 'No admitted students found in the selected class. Please ensure there are admitted students before creating fees.'
                    ])->withInput();
                }

                $createdFees = [];
                foreach ($students as $student) {
                    $feeData = [
                        'student_id' => $student->id,
                        'class_id' => $validated['class_id'],
                        'type' => $validated['type'],
                        'amount' => $validated['amount'],
                        'status' => 'unpaid',
                        'due_date' => $validated['due_date'],
                        'description' => $validated['description'] ?? null,
                    ];

                    Log::info('Creating fee with data:', $feeData);

                    $fee = Fee::create($feeData);

                    Log::info('Fee created successfully:', ['fee_id' => $fee->id, 'student_id' => $fee->student_id]);

                    $createdFees[] = $fee;
                }

                Log::info('All fees created successfully. Total created:', ['count' => count($createdFees)]);

                return redirect()->route('fees.index')
                    ->with('success', "Fee created successfully for {$students->count()} students in the selected class.");
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Error creating fees:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to create fees. Please try again.'])->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $fee = Fee::with(['student.school', 'student.class'])->findOrFail($id);

        return Inertia::render('Fees/Show', [
            'fee' => $fee,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $fee = Fee::with(['student.school', 'student.class'])->findOrFail($id);

        return Inertia::render('Fees/Edit', [
            'fee' => $fee,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeeRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $fee = Fee::findOrFail($id);
                $validated = $request->validated();

                $data = [
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'status' => $validated['status'],
                    'due_date' => $validated['due_date'],
                    'description' => $validated['description'] ?? null,
                ];

                // Set paid_at if status is paid and it's not already set
                if ($validated['status'] === 'paid' && !$fee->paid_at) {
                    $data['paid_at'] = now();
                } elseif ($validated['status'] !== 'paid') {
                    $data['paid_at'] = null;
                }

                // Set paid_amount and paid_date if provided
                if (isset($validated['paid_amount'])) {
                    $data['paid_amount'] = $validated['paid_amount'];
                }
                if (isset($validated['paid_date'])) {
                    $data['paid_date'] = $validated['paid_date'];
                }

                $fee->update($data);

                return redirect()->route('fees.index')
                    ->with('success', 'Fee updated successfully.');
            }, 5); // 5 retries for deadlock handling
        } catch (\Exception $e) {
            Log::error('Error updating fee:', [
                'error' => $e->getMessage(),
                'fee_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to update fee. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $fee = Fee::findOrFail($id);

        // Only allow deletion if fee is unpaid
        if ($fee->status === 'paid') {
            return back()->withErrors(['error' => 'Cannot delete a paid fee.']);
        }

        $fee->delete();

        return redirect()->route('fees.index')
            ->with('success', 'Fee deleted successfully.');
    }

    /**
     * Get classes for a specific school
     */
    public function getClasses(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $school = School::with(['classes' => function ($q) {
            $q->orderBy('name');
        }])->find($request->school_id);

        $classes = $school ? $school->classes : collect();

        return response()->json($classes);
    }

    /**
     * Get students for a specific class and school
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $students = Student::where('school_id', $request->school_id)
            ->where('class_id', $request->class_id)
            ->where('status', 'admitted')
            ->select('id', 'name', 'registration_number')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }
}

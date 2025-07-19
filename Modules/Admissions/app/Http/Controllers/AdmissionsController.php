<?php

namespace Modules\Admissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\App\Models\Student;
use Modules\Schools\App\Models\School;
use Modules\ClassesSections\App\Models\ClassModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\App\Models\FeeItem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AdmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with('class'); // Eager load class relationship
        // Filter by school_id
        if ($request->has('school_id') && $request->input('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }
        // Filter by class_id
        if ($request->has('class_id') && $request->input('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        // Filter by status
        if ($request->has('status') && $request->input('status')) {
            $query->where('status', $request->input('status'));
        }
        // Filter by year (admission_date)
        if ($request->has('year') && $request->input('year')) {
            $query->whereYear('admission_date', $request->input('year'));
        }
        // Multi-column search
        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('registration_number', 'like', "%$search%")
                    ->orWhere('father_name', 'like', "%$search%")
                    ->orWhere('mobile_no', 'like', "%$search%")
                    ->orWhere('b_form_number', 'like', "%$search%")
                    ->orWhere('father_cnic', 'like', "%$search%")
                    ->orWhere('guardian_name', 'like', "%$search%")
                    ->orWhere('previous_school', 'like', "%$search%")
                ;
            });
        }
        // Pagination
        $students = $query->orderByDesc('id')->paginate($request->input('per_page', 12));
        return Inertia::render('admissions/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'nationality' => 'required|string',
            'registration_number' => 'required|string|unique:students',
            'name' => 'required|string',
            'b_form_number' => 'required|string|unique:students',
            'admission_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'class_shift' => 'required|string',
            'previous_school' => 'nullable|string',
            'inclusive' => 'required|string',
            'other_inclusive_type' => 'nullable|string',
            'religion' => 'required|string',
            'is_bricklin' => 'boolean',
            'is_orphan' => 'boolean',
            'is_qsc' => 'boolean',
            'profile_photo_path' => 'nullable|file|image|max:2048',
            'father_name' => 'required|string',
            'guardian_name' => 'nullable|string',
            'father_cnic' => 'required|string',
            'mother_cnic' => 'nullable|string',
            'father_profession' => 'required|string',
            'no_of_children' => 'nullable|integer',
            'job_type' => 'nullable|string',
            'father_education' => 'required|string',
            'mother_education' => 'required|string',
            'mother_profession' => 'required|string',
            'father_income' => 'required|string',
            'mother_income' => 'nullable|string',
            'household_income' => 'required|string',
            'permanent_address' => 'required|string',
            'phone_no' => 'nullable|string',
            'mobile_no' => 'required|string',
        ]);

        // Handle file upload
        if ($request->hasFile('profile_photo_path')) {
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        } else {
            $validated['profile_photo_path'] = null;
        }

        $student = Student::create($validated);

        // Always create a fee for admission
        $fee = Fee::create([
            'student_id' => $student->id,
            'type' => 'admission',
            'amount' => 500, // Example, replace with your logic
            'status' => 'unpaid',
            'due_date' => now()->addDays(7),
        ]);
        FeeItem::create([
            'fee_id' => $fee->id,
            'description' => 'Admission Fee',
            'amount' => 500,
        ]);

        Broadcast::event('student.created', $student);
        return redirect()->route('admissions.index')->with('success', 'Student admitted successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $schools = School::all();
        $classes = ClassModel::all();
        return Inertia::render('admissions/Edit', [
            'student' => $student,
            'schools' => $schools,
            'classes' => $classes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'nationality' => 'required|string',
            'registration_number' => 'required|string|unique:students,registration_number,' . $id,
            'name' => 'required|string',
            'b_form_number' => 'required|string|unique:students,b_form_number,' . $id,
            'admission_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'class_shift' => 'required|string',
            'previous_school' => 'nullable|string',
            'inclusive' => 'required|string',
            'other_inclusive_type' => 'nullable|string',
            'religion' => 'required|string',
            'is_bricklin' => 'required|boolean',
            'is_orphan' => 'required|boolean',
            'is_qsc' => 'required|boolean',
            'profile_photo_path' => 'nullable|file|image|max:2048',
            'father_name' => 'required|string',
            'guardian_name' => 'nullable|string',
            'father_cnic' => 'required|string',
            'mother_cnic' => 'nullable|string',
            'father_profession' => 'required|string',
            'no_of_children' => 'nullable|integer',
            'job_type' => 'nullable|string',
            'father_education' => 'required|string',
            'mother_education' => 'required|string',
            'mother_profession' => 'required|string',
            'father_income' => 'required|string',
            'mother_income' => 'nullable|string',
            'household_income' => 'required|string',
            'permanent_address' => 'required|string',
            'phone_no' => 'nullable|string',
            'mobile_no' => 'required|string',
        ]);

        // Handle file upload
        if ($request->hasFile('profile_photo_path')) {
            // Remove old image if it exists
            if ($student->profile_photo_path && Storage::disk('public')->exists($student->profile_photo_path)) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }
            $path = $request->file('profile_photo_path')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        } else {
            // If not uploading a new file, keep the old path
            $validated['profile_photo_path'] = $student->profile_photo_path;
        }

        $student->update($validated);
        Broadcast::event('student.updated', $student);
        return redirect()->route('admissions.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        Broadcast::event('student.deleted', ['id' => $id]);
        return redirect()->route('admissions.index')->with('success', 'Student deleted successfully.');
    }

    /**
     * Approve an applicant and generate fee/fee items.
     */
    public function approve(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $fee = Fee::where('student_id', $student->id)->where('type', 'admission')->first();
        if (!$fee) {
            return response()->json(['success' => false, 'message' => 'Fee record not found.'], 422);
        }
        $validated = $request->validate([
            'paid_voucher_image' => 'required|file|image|max:2048',
        ]);
        // Save paid voucher image
        if ($request->hasFile('paid_voucher_image')) {
            $voucherPath = $request->file('paid_voucher_image')->store('vouchers', 'public');
            $fee->paid_voucher_image = $voucherPath;
            $fee->status = 'paid';
            $fee->save();
        }
        if ($fee->status !== 'paid' || !$fee->paid_voucher_image) {
            return response()->json(['success' => false, 'message' => 'Fee must be paid and voucher uploaded before approval.'], 422);
        }
        $student->status = 'admitted';
        $student->save();
        Broadcast::event('student.updated', $student);
        return response()->json(['success' => true, 'message' => 'Student approved successfully.']);
    }
}

<?php

namespace Modules\Admissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\App\Models\FeeItem;
use Illuminate\Support\Facades\Config;

class AdmissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();
        // Filtering
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%")
                ->orWhere('registration_number', 'like', "%$search%")
                ->orWhere('father_name', 'like', "%$search%")
                ->orWhere('class', 'like', "%$search%")
                ->orWhere('mobile_no', 'like', "%$search%")
                ->orWhere('b_form_number', 'like', "%$search%")
                ->orWhere('father_cnic', 'like', "%$search%")
                ->orWhere('guardian_name', 'like', "%$search%")
                ->orWhere('previous_school', 'like', "%$search%")
            ;
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
            'nationality' => 'required|string',
            'registration_number' => 'required|string|unique:students',
            'name' => 'required|string',
            'b_form_number' => 'required|string|unique:students',
            'admission_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'class' => 'required|string',
            'gender' => 'required|string',
            'class_shift' => 'required|string',
            'previous_school' => 'nullable|string',
            'inclusive' => 'required|string',
            'other_inclusive_type' => 'nullable|string',
            'religion' => 'required|string',
            'is_bricklin' => 'boolean',
            'is_orphan' => 'boolean',
            'is_qsc' => 'boolean',
            'profile_photo_path' => 'nullable|string',
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
        $student = Student::create($validated);

        // Check config for fee generation logic
        if (Config::get('admissions.admission_fee_generation') === 'immediate') {
            // Use full payment by default, can be customized
            $amount = 500; // Example default, replace with your logic
            $fee = Fee::create([
                'student_id' => $student->id,
                'type' => 'admission',
                'amount' => $amount,
                'status' => 'unpaid',
                'due_date' => now()->addDays(7),
            ]);
            FeeItem::create([
                'fee_id' => $fee->id,
                'description' => 'Admission Fee',
                'amount' => $amount,
            ]);
        }
        Broadcast::event('student.created', $student);
        return Redirect::route('admissions.index')->with('toast', [
            'type' => 'success',
            'message' => 'Student admitted successfully.'
        ]);
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
        return Inertia::render('admissions/Edit', [
            // 'students' => $students,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'nationality' => 'required|string',
            'registration_number' => 'required|string|unique:students,registration_number,' . $id,
            'name' => 'required|string',
            'b_form_number' => 'required|string|unique:students,b_form_number,' . $id,
            'admission_date' => 'required|date',
            'date_of_birth' => 'required|date',
            'class' => 'required|string',
            'gender' => 'required|string',
            'class_shift' => 'required|string',
            'previous_school' => 'nullable|string',
            'inclusive' => 'required|string',
            'other_inclusive_type' => 'nullable|string',
            'religion' => 'required|string',
            'is_bricklin' => 'boolean',
            'is_orphan' => 'boolean',
            'is_qsc' => 'boolean',
            'profile_photo_path' => 'nullable|string',
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
        $student->update($validated);
        Broadcast::event('student.updated', $student);
        return Redirect::route('admissions.index')->with('toast', [
            'type' => 'success',
            'message' => 'Student updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        Broadcast::event('student.deleted', ['id' => $id]);
        return Redirect::route('admissions.index')->with('toast', [
            'type' => 'success',
            'message' => 'Student deleted successfully.'
        ]);
    }

    /**
     * Approve an applicant and generate fee/fee items.
     */
    public function approve(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'payment_method' => 'required|in:full,installments',
            'installments' => 'nullable|array',
            'installments.*.amount' => 'required_if:payment_method,installments|numeric|min:1',
            'installments.*.due_date' => 'required_if:payment_method,installments|date',
        ]);

        // Create Fee
        $fee = Fee::create([
            'student_id' => $student->id,
            'type' => 'admission',
            'amount' => $validated['payment_method'] === 'full'
                ? $request->input('installments.0.amount')
                : collect($validated['installments'])->sum('amount'),
            'status' => 'unpaid',
            'due_date' => $validated['payment_method'] === 'full'
                ? $request->input('installments.0.due_date')
                : collect($validated['installments'])->min('due_date'),
        ]);

        // Create FeeItems
        if ($validated['payment_method'] === 'full') {
            FeeItem::create([
                'fee_id' => $fee->id,
                'description' => 'Admission Fee',
                'amount' => $request->input('installments.0.amount'),
            ]);
        } else {
            foreach ($validated['installments'] as $i => $item) {
                FeeItem::create([
                    'fee_id' => $fee->id,
                    'description' => 'Installment ' . ($i + 1),
                    'amount' => $item['amount'],
                ]);
            }
        }

        $student->status = 'admitted';
        $student->save();

        // Redirect to voucher page (to be implemented)
        return Redirect::route('fees.voucher', ['fee' => $fee->id]);
    }
}

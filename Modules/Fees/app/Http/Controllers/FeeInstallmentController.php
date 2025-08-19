<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Admissions\App\Models\Student;
use Modules\Fees\App\Models\Fee;
use Modules\Fees\Http\Requests\StoreFeeInstallmentRequest;
use Modules\Fees\Models\FeeInstallment;

class FeeInstallmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $registrationNumber = $request->input('registration_number');

        $student = Student::where('registration_number', $registrationNumber)->first();

        if (!$student) {
            return Inertia::render('FeeInstallments/Index', [
                'student' => null,
                'fee' => null,
                'fee_items' => [],
                'installments' => [],
                'error' => 'Student not found.'
            ]);
        }


        $fee = Fee::with('feeItems')->where('student_id', $student->id)->where('type', '=', 'monthly')->first();
        $feeItems = $fee ? $fee->feeItems : collect([]);
        $installments = FeeInstallment::where('student_id', $student->id)->get();
        // dd($fee, $feeItems, $installments);
        return Inertia::render('FeeInstallments/Index', [
            'student' => $student,
            'fee' => $fee,
            'fee_items' => $feeItems,
            'installments' => $installments,
            'error' => null
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fees::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeInstallmentRequest $request)
    {
        $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'installments' => 'required|array|min:1',
            'installments.*.amount' => 'required|numeric|min:1',
            'installments.*.due_date' => 'required|date',
        ]);

        $fee = Fee::findOrFail($request->fee_id);
        $studentId = $fee->student_id;

        foreach ($request->installments as $installment) {
            FeeInstallment::create([
                'fee_id' => $fee->id,
                'student_id' => $studentId,
                'amount' => $installment['amount'],
                'due_date' => $installment['due_date'],
                'status' => 'unpaid',
            ]);
        }

        return back()->with('success', 'Installments created successfully.');
    }

    public function markAsPaid(FeeInstallment $installment, Request $request)
    {
        $installment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'voucher_number' => $request->voucher_number,
            'paid_voucher_image' => $request->paid_voucher_image, // You'll need to handle upload
        ]);

        return back()->with('success', 'Installment marked as paid.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('fees::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('fees::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

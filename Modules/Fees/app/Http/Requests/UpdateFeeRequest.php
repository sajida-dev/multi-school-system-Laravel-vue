<?php

namespace Modules\Fees\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:admission,tuition,papers'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
            'status' => ['required', 'string', 'in:unpaid,paid,partial,overdue'],
            'description' => ['nullable', 'string', 'max:255'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'paid_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Fee type is required.',
            'type.in' => 'Fee type must be admission, tuition, or papers.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.',
            'amount.max' => 'Amount cannot exceed 999,999.99.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be today or in the future.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be unpaid, paid, partial, or overdue.',
            'description.max' => 'Description cannot exceed 255 characters.',
            'paid_amount.numeric' => 'Paid amount must be a number.',
            'paid_amount.min' => 'Paid amount must be at least 0.',
            'paid_amount.max' => 'Paid amount cannot exceed 999,999.99.',
            'paid_date.date' => 'Paid date must be a valid date.',
            'paid_date.before_or_equal' => 'Paid date cannot be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'fee type',
            'amount' => 'amount',
            'due_date' => 'due date',
            'status' => 'status',
            'description' => 'description',
            'paid_amount' => 'paid amount',
            'paid_date' => 'paid date',
        ];
    }
}

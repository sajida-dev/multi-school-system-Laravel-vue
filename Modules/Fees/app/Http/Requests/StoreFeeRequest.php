<?php

namespace Modules\Fees\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'in:admission,tuition,papers'
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->format('Y-m-d')
            ],
            'school_id' => [
                'required',
                'exists:schools,id'
            ],
            'class_id' => [
                'required',
                'exists:classes,id'
            ],
            'description' => [
                'nullable',
                'string',
                'max:255'
            ],
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
            'school_id.required' => 'School is required.',
            'school_id.exists' => 'Selected school does not exist.',
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'description.max' => 'Description cannot exceed 255 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'fee type',
            'amount' => 'amount',
            'due_date' => 'due date',
            'school_id' => 'school',
            'class_id' => 'class',
            'description' => 'description',
        ];
    }
}

<?php

namespace App\Http\Requests\Modules\Admissions\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'gender' => $this->normalizeEnumValue($this->gender),
            'religion' => $this->normalizeEnumValue($this->religion),
            'blood_group' => $this->blood_group ? $this->normalizeEnumValue($this->blood_group) : null,
        ]);
    }

    private function normalizeEnumValue($value)
    {
        if (!$value) return $value;

        // Convert to lowercase and replace spaces/slashes with underscores
        return strtolower(str_replace([' ', '/'], '_', trim($value)));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_number' => [
                'required',
                'string',
                'max:12',
                'regex:/^[A-Za-z0-9]+$/',
                'unique:students,registration_number'
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'b_form_number' => [
                'required',
                'string',
                'regex:/^\d{5}-\d{7}-\d$/'
            ],
            'father_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'guardian_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'father_cnic' => [
                'required',
                'string',
                'regex:/^\d{5}-\d{7}-\d$/'
            ],
            'mother_cnic' => [
                'required',
                'string',
                'regex:/^\d{5}-\d{7}-\d$/'
            ],
            'no_of_children' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'permanent_address' => [
                'required',
                'string',
                'min:10',
                'max:500'
            ],
            'phone_no' => [
                'nullable',
                'string',
                'regex:/^\+?[0-9\s\-\(\)]+$/'
            ],
            'mobile_no' => [
                'required',
                'string',
                'regex:/^03\d{9}$/'
            ],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'gender' => [
                'required',
                'in:male,female,other'
            ],
            'religion' => [
                'required',
                'in:islam,christianity,hinduism,other'
            ],
            'blood_group' => [
                'nullable',
                'in:A+,A-,B+,B-,AB+,AB-,O+,O-'
            ],
            'previous_school' => [
                'nullable',
                'string',
                'max:255'
            ],
            'admission_date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'class_id' => [
                'required',
                'exists:classes,id'
            ],
            'section_id' => [
                'required',
                'exists:sections,id'
            ],
            'profile_photo_path' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048'
            ],
            'school_id' => [
                'nullable',
                'exists:schools,id'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_number.required' => 'Registration number is required.',
            'registration_number.regex' => 'Registration number must contain only letters and numbers.',
            'registration_number.unique' => 'This registration number is already taken.',
            'name.required' => 'Student name is required.',
            'name.regex' => 'Name must contain only letters and spaces.',
            'b_form_number.required' => 'B-Form number is required.',
            'b_form_number.regex' => 'B-Form number must be in format: 12345-6789012-3',
            'father_name.required' => 'Father name is required.',
            'father_name.regex' => 'Father name must contain only letters and spaces.',
            'guardian_name.required' => 'Guardian name is required.',
            'guardian_name.regex' => 'Guardian name must contain only letters and spaces.',
            'father_cnic.required' => 'Father CNIC is required.',
            'father_cnic.regex' => 'Father CNIC must be in format: 12345-6789012-3',
            'mother_cnic.required' => 'Mother CNIC is required.',
            'mother_cnic.regex' => 'Mother CNIC must be in format: 12345-6789012-3',
            'no_of_children.required' => 'Number of children is required.',
            'no_of_children.min' => 'Number of children must be at least 1.',
            'no_of_children.max' => 'Number of children cannot exceed 20.',
            'permanent_address.required' => 'Permanent address is required.',
            'permanent_address.min' => 'Address must be at least 10 characters long.',
            'mobile_no.required' => 'Mobile number is required.',
            'mobile_no.regex' => 'Mobile number must start with 03 and be 11 digits.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Date of birth must be after 1900.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be male, female, or other.',
            'religion.required' => 'Religion is required.',
            'religion.in' => 'Please select a valid religion.',
            'blood_group.in' => 'Blood group must be a valid type.',
            'admission_date.required' => 'Admission date is required.',
            'admission_date.before_or_equal' => 'Admission date cannot be in the future.',
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Selected section does not exist.',
            'profile_photo_path.image' => 'Profile photo must be an image.',
            'profile_photo_path.mimes' => 'Profile photo must be JPEG, PNG, JPG, or GIF.',
            'profile_photo_path.max' => 'Profile photo must not exceed 2MB.',
            'school_id.exists' => 'Selected school does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'registration_number' => 'registration number',
            'name' => 'student name',
            'b_form_number' => 'B-Form number',
            'father_name' => 'father name',
            'guardian_name' => 'guardian name',
            'father_cnic' => 'father CNIC',
            'mother_cnic' => 'mother CNIC',
            'no_of_children' => 'number of children',
            'permanent_address' => 'permanent address',
            'phone_no' => 'phone number',
            'mobile_no' => 'mobile number',
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
            'religion' => 'religion',
            'blood_group' => 'blood group',
            'previous_school' => 'previous school',
            'admission_date' => 'admission date',
            'class_id' => 'class',
            'section_id' => 'section',
            'profile_photo_path' => 'profile photo',
            'school_id' => 'school',
        ];
    }
}

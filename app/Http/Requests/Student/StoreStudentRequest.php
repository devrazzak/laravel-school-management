<?php

namespace App\Http\Requests\Student;

use App\Enums\StudentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'department' => ['nullable', 'string', 'max:100'],
            'cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'status' => ['nullable', Rule::enum(StudentStatus::class)],

        ];
    }

    public function messages(): array
    {
        return [
            // 'email.unique' => 'The email has already been taken.',
            // 'date_of_birth.before' => 'The date of birth must be a date before today.',
            // 'cgpa.between' => 'The CGPA must be between 0 and 4.',
        ];
    }
}

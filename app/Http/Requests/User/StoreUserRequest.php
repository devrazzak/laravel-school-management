<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        $base = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::enum(UserRole::class)],
            'status' => ['nullable', 'string', Rule::enum(UserStatus::class)],
            'profile' => ['nullable', 'array'],
        ];

        return match ($this->enum('role', UserRole::class)) {
            UserRole::Student => [
                ...$base,
                'profile.registration_number' => ['prohibited'],
                'profile.department' => ['nullable', 'string', 'max:100'],
                'profile.cgpa' => ['nullable', 'numeric', 'between:0,4'],
                'profile.phone' => ['nullable', 'string', 'max:20'],
                'profile.date_of_birth' => ['nullable', 'date', 'before:today'],
                'profile.gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
                'profile.profile_picture' => ['nullable', 'string', 'max:255'],
            ],
            UserRole::Teacher => [
                ...$base,
                'profile.employee_id' => ['prohibited'],
                'profile.designation' => ['nullable', 'string', 'max:100'],
                'profile.department' => ['nullable', 'string', 'max:100'],
                'profile.phone' => ['nullable', 'string', 'max:20'],
                'profile.joining_date' => ['nullable', 'date'],
                'profile.profile_picture' => ['nullable', 'string', 'max:255'],
            ],
            default => $base,
        };
    }
}

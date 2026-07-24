<?php

namespace App\Http\Requests;

use App\Enums\MissingPersonStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMissingPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'case_number' => ['prohibited'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'last_seen_location' => ['required', 'string', 'max:255'],
            'last_seen_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', Rule::in(array_column(MissingPersonStatus::cases(), 'value'))],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'The full name is required.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Gender must be either male or female.',
            'last_seen_location.required' => 'The last seen location is required.',
            'last_seen_date.required' => 'The last seen date is required.',
            'last_seen_date.before_or_equal' => 'The last seen date cannot be in the future.',
            'date_of_birth.before' => 'The date of birth must be before today.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The selected status is invalid.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The photo must be a JPG or PNG file.',
            'photo.max' => 'The photo must not exceed 5MB.',
        ];
    }
}

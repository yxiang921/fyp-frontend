<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicMissingPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'last_seen_location' => ['required', 'string', 'max:255'],
            'last_seen_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'website' => ['nullable', 'string', 'max:0', 'prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Please provide the full name of the missing person.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Gender must be either male or female.',
            'last_seen_location.required' => 'Please provide the last seen location.',
            'last_seen_date.required' => 'Please provide the last seen date.',
            'last_seen_date.before_or_equal' => 'The last seen date cannot be in the future.',
            'date_of_birth.before' => 'The date of birth must be before today.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The photo must be a JPG or PNG file.',
            'photo.max' => 'The photo must not exceed 5MB.',
            'website.prohibited' => 'Automated submissions are not allowed.',
        ];
    }
};

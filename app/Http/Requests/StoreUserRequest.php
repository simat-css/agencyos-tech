<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            // Company
            'company_id' => [
                'required',
                'exists:companies,id'
            ],

            // Department
            'department_id' => [
                'required',
                'exists:departments,id'
            ],

            // User Information
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],

            // Authentication
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],

            // Role Assignment
            'role' => [
                'required',
                'exists:roles,name'
            ],

            // Status
            'status' => [
                'required',
                'boolean'
            ],

            // Profile Photo
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'company_id.required' =>
                'Please select a company.',

            'department_id.required' =>
                'Please select a department.',

            'role.required' =>
                'Please select a role.',

            'email.unique' =>
                'This email already exists.',

            'password.confirmed' =>
                'Password confirmation does not match.',

            'profile_photo.image' =>
                'Profile photo must be an image file.',
        ];
    }
}
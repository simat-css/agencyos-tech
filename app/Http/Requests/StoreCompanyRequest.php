<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules.
     */
    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'unique:companies,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'boolean',
            ],

        ];
    }

    /**
     * Custom Messages.
     */
    public function messages(): array
    {
        return [

            'name.required' => 'Company name is required.',

            'email.email' => 'Please enter a valid email address.',

            'email.unique' => 'This email is already registered.',

            'website.url' => 'Please enter a valid website URL.',

            'logo.image' => 'Logo must be an image.',

            'logo.max' => 'Logo size must not exceed 2MB.',

        ];
    }
}
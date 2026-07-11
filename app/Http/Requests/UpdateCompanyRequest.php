<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => 'required|string|max:150',

'email' => [
    'required',
    'email',
    Rule::unique('companies')
        ->ignore($this->company)
],

'phone' => 'required|string|max:20',

'website' => 'nullable|url|max:255',

'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

'address' => 'required|string',

'status' => 'required|boolean',
        ];
    }

    public function messages(): array
{
    return [
        'name.required' => 'Company name is required.',
        'email.required' => 'Company email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already registered.',
        'phone.required' => 'Company phone is required.',
        'address.required' => 'Company address is required.',
        'website.url' => 'Please enter a valid website URL.',
    ];
}
}
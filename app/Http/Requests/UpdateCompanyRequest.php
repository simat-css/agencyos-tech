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
                'nullable',
                'email',
                Rule::unique('companies')
                    ->ignore($this->company)
            ],

            'phone' => 'nullable|string|max:20',

            'website' => 'nullable|url',

            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'address' => 'nullable|string',

            'status' => 'required|boolean',
        ];
    }
}
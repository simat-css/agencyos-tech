<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
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

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'name' => [

                'required',

                'string',

                'max:150',

                Rule::unique('departments')
                    ->where(function ($query) {

                        return $query->where(
                            'company_id',
                            $this->company_id
                        );
                    })
                    ->ignore($this->department),
            ],

            'code' => [
                'nullable',
                'string',
                'max:30',
            ],

            'description' => [
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

            'company_id.required' => 'Please select a company.',

            'company_id.exists' => 'Selected company does not exist.',

            'name.required' => 'Department name is required.',

            'name.unique' => 'This department already exists for the selected company.',

            'status.required' => 'Please select department status.',
        ];
    }
}
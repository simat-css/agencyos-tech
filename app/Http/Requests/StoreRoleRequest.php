<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:100',
                'unique:roles,name',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'exists:permissions,name',
            ],

        ];
    }


    public function messages(): array
    {
        return [

            'name.required' =>
                'Role name is required.',

            'name.unique' =>
                'This role already exists.',

            'permissions.*.exists' =>
                'Invalid permission selected.',

        ];
    }
}
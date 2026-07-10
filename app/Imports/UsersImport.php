<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{

    public $imported = 0;
    public $skipped = 0;


    public function model(array $row)
    {

        // Required Fields
        if (
            empty($row['name']) ||
            empty($row['email']) ||
            empty($row['company_id']) ||
            empty($row['department_id']) ||
            empty($row['role'])
        ) {

            $this->skipped++;

            return null;
        }


        // Duplicate Email
        if (User::where('email', $row['email'])->exists()) {

            $this->skipped++;

            return null;
        }


        // Company Check
        if (!Company::find($row['company_id'])) {

            $this->skipped++;

            return null;
        }


        // Department Check
        if (!Department::find($row['department_id'])) {

            $this->skipped++;

            return null;
        }


        $user = User::create([

            'name'          => $row['name'],
            'email'         => $row['email'],
            'company_id'    => $row['company_id'],
            'department_id' => $row['department_id'],
            'status'        => $row['status'] ?? 1,

            'password' => Hash::make(
                $row['password'] ?? 'Password@123'
            ),

        ]);


        $user->assignRole($row['role']);


        $this->imported++;


        return $user;
    }
}
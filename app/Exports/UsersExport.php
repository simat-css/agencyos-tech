<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
{
    $query = User::with([
        'company',
        'department',
        'roles'
    ]);

    if (!auth()->user()->hasRole('Super Admin')) {
        $query->where(
            'company_id',
            auth()->user()->company_id
        );
    }

    return $query->get()->map(function ($user) {

        return [
            'name'       => $user->name,
            'email'      => $user->email,
            'company'    => $user->company?->name,
            'department' => $user->department?->name,
            'role'       => $user->roles->pluck('name')->implode(', '),
            'status'     => $user->status ? 'Active' : 'Inactive',
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
        ];
    });
}
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Company',
            'Department',
            'Role',
            'Status',
            'Created At',
        ];
    }
}
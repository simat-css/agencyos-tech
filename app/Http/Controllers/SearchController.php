<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Spatie\Permission\Models\Role;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->q;

        if (!$keyword) {
            return response()->json([]);
        }

        $results = [];

        // Users
        $users = User::where('name', 'LIKE', "%{$keyword}%")
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => 'User',
                'name' => $user->name,
                'icon' => 'fas fa-user',
                'url'  => route('users.show', $user),
                'subtitle' => $user->email,
            ];
        }

        // Companies
        $companies = Company::where('name', 'LIKE', "%{$keyword}%")
            ->limit(5)
            ->get();

        foreach ($companies as $company) {
            $results[] = [
                'type' => 'Company',
                'name' => $company->name,
                'icon' => 'fas fa-building',
                'url'  => route('companies.show', $company),
                'subtitle' => 'Company',
            ];
        }

        // Departments
        $departments = Department::with('company')
    ->where('name','LIKE',"%{$keyword}%")
    ->limit(5)
    ->get();

       foreach ($departments as $department) {

    $results[] = [
        'type' => 'Department',
        'name' => $department->name,
        'icon' => 'fas fa-sitemap',
        'url'  => route('departments.show', $department),
        'subtitle' => $department->company->name ?? 'No Company',
    ];
}

        // Roles
        $roles = Role::where('name', 'LIKE', "%{$keyword}%")
            ->limit(5)
            ->get();

        foreach ($roles as $role) {
            $results[] = [
                'type' => 'Role',
                'name' => $role->name,
                'icon' => 'fas fa-user-shield',
                'url'  => route('roles.show', $role),
                'subtitle' => 'Role',
            ];
        }

        return response()->json($results);
    }
}
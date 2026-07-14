<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompanyService
{
    /**
     * Get all companies
     */
    public function getAll()
{
    return Company::with(
        'creator'
    )
    ->latest()
    ->paginate(10);
}

    /**
     * Create company
     */
    public function create(array $data): Company
    {
        return DB::transaction(function () use ($data) {

            // Upload Logo
            if (!empty($data['logo'])) {

                $data['logo'] = $data['logo']->store(
                    'companies',
                    'public'
                );
            }

            $data['created_by'] = Auth::id();
            return Company::create($data);
        });
    }

    /**
     * Update company
     */
    public function update(
        Company $company,
        array $data
    ): Company {

        return DB::transaction(function () use (
            $company,
            $data
        ) {

            // Replace Logo
            if (!empty($data['logo'])) {

                // Delete old logo
                if (
                    $company->logo &&
                    Storage::disk('public')->exists($company->logo)
                ) {
                    Storage::disk('public')
                        ->delete($company->logo);
                }

                $data['logo'] = $data['logo']->store(
                    'companies',
                    'public'
                );
            }

            $data['updated_by'] = Auth::id();

            $company->update($data);

            return $company->fresh();
        });
    }

    /**
     * Delete company
     */
    public function delete(Company $company): bool
{
    return DB::transaction(function () use ($company) {

        // Delete logo
        if (
            $company->logo &&
            Storage::disk('public')->exists($company->logo)
        ) {
            Storage::disk('public')->delete($company->logo);
        }

        // Remove department assignment from users
        $company->users()->update([
            'department_id' => null,
        ]);

        // Soft delete departments
        $company->departments()->delete();

        // Soft delete company
        return $company->delete();
    });
}

 public function toggleStatus(Company $company): Company
{
    return DB::transaction(function () use ($company) {

        $newStatus = ! $company->status;

        $company->update([
            'status'     => $newStatus,
            'updated_by' => Auth::id(),
        ]);

        // Company Deactivated
        if (!$newStatus) {

            $company->departments()
                ->where('status', 1)
                ->update([
                    'status' => 0,
                    'auto_deactivated' => true,
                    'updated_by' => Auth::id(),
                ]);
        }

        return $company->fresh();
    });
}

//AI Company Module
//Find Company By ID
public function findById(
    int $id
): ?Company {

    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {

        $query->where(
            'id',
            auth()->user()->company_id
        );

    }

    return $query->find($id);

}
//Find Company By Name
public function findByName(
    string $companyName
): ?Company {

    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {

        $query->where(
            'id',
            auth()->user()->company_id
        );

    }

    return $query
        ->where(
            'name',
            'like',
            "%{$companyName}%"
        )
        ->first();

}
//Check Email Exists
public function emailExists(
    string $email
): bool {

  return Company::withTrashed()
    ->where('email', $email)
    ->exists();

}
public function restore(Company $company): bool
{
    return DB::transaction(function () use ($company) {

        return $company->restore();

    });
}
//Email Exists Except Current Company
public function emailExistsExcept(
    string $email,
    int $companyId
): bool {

    return Company::where(
        'email',
        $email
    )
    ->where(
        'id',
        '!=',
        $companyId
    )
    ->exists();

}
//Get Active Companies
public function getActiveCompanies()
{
    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {

        $query->where(
            'id',
            auth()->user()->company_id
        );

    }

    return $query
        ->where(
            'status',
            1
        )
        ->orderBy('name')
        ->get();
}
//Get Inactive Companies
public function getInactiveCompanies()
{
    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {

        $query->where(
            'id',
            auth()->user()->company_id
        );

    }

    return $query
        ->where(
            'status',
            0
        )
        ->orderBy('name')
        ->get();
}
//Company Statistics
public function getStatistics(): array
{
    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {

        $query->where(
            'id',
            auth()->user()->company_id
        );

    }

    return [

        'total' =>
            (clone $query)->count(),

        'active' =>
            (clone $query)
                ->where('status', 1)
                ->count(),

        'inactive' =>
            (clone $query)
                ->where('status', 0)
                ->count()

    ];
}
public function searchCompanies(
    string $keyword
)
{
    $query = Company::query();

    if (
        auth()->check()
        &&
        auth()->user()->hasRole(
            'Company Admin'
        )
    ) {
        $query->where(
            'id',
            auth()->user()->company_id
        );
    }

    return $query
        ->where(
            'name',
            'like',
            "%{$keyword}%"
        )
        ->get();
}
}
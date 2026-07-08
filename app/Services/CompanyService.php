<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        // Delete logo file
        if (
            $company->logo &&
            Storage::disk('public')->exists($company->logo)
        ) {
            Storage::disk('public')->delete($company->logo);
        }

        // Soft delete all departments
        $company->departments()->delete();

        // Soft delete company
        return $company->delete();
    });
}

  public function toggleStatus(Company $company): Company
{
    return DB::transaction(function () use ($company) {

        // Toggle Company Status
        $newStatus = ! $company->status;

        $company->update([
            'status' => $newStatus,
        ]);

        // Company Deactivate
        if (!$newStatus) {

            $company->departments()
                ->where('status', 1)
                ->update([
                    'status' => 0,
                    'auto_deactivated' => 1,
                ]);

        }

        // Company Activate
        else {

            $company->departments()
                ->where('auto_deactivated', 1)
                ->update([
                    'status' => 1,
                    'auto_deactivated' => 0,
                ]);

        }

        return $company->fresh();
    });
}
}
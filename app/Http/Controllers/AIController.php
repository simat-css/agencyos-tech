<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AI\CompanyAIService;
use App\Services\AI\DepartmentAIService;

class AIController extends Controller
{
    protected CompanyAIService $companyAIService;
    protected DepartmentAIService $departmentAIService;

    public function __construct(
        CompanyAIService $companyAIService,
        DepartmentAIService $departmentAIService
    ) {

        $this->companyAIService = $companyAIService;
        $this->departmentAIService = $departmentAIService;

    }

    public function execute(Request $request)
    {
        $request->validate([
            'command' => 'required|string|max:1000'
        ]);

        $command = strtolower(
            trim($request->command)
        );

        /*
        |--------------------------------------------------------------------------
        | Pending Department Confirmations
        |--------------------------------------------------------------------------
        */
if (
    $command === 'confirm'
    &&
    (
        session()->has('pending_department_update')
        ||
        session()->has('pending_department_delete')
        ||
        session()->has('pending_department_status_update')
    )
) {

    $result =
        $this->departmentAIService->process(
            $request->command
        );

}

        /*
        |--------------------------------------------------------------------------
        | Department Commands
        |--------------------------------------------------------------------------
        */

        elseif (
            str_contains(
                $command,
                'department'
            )
        ) {

            $result =
                $this->departmentAIService->process(
                    $request->command
                );

        }

        /*
        |--------------------------------------------------------------------------
        | Company Commands
        |--------------------------------------------------------------------------
        */

        else {

            $result =
                $this->companyAIService->process(
                    $request->command
                );

        }

        return response()->json(
            $result
        );
    }
}
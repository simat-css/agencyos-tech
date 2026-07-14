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
        'command' => 'required|string|max:1000',
    ]);

    $command = strtolower(trim($request->command));


    /*
    |--------------------------------------------------------------------------
    | CONFIRM ACTIONS
    |--------------------------------------------------------------------------
    */

    if ($command === 'confirm') {


        // Company Pending Actions First
        if (
            session()->has('pending_company_delete') ||
            session()->has('pending_company_update') ||
            session()->has('pending_company_activate') ||
            session()->has('pending_company_deactivate')||
            session()->has('pending_company_restore')
        ) {

            $result = $this->companyAIService
                ->process($request->command);

        }


        // Department Pending Actions
        elseif (
            session()->has('pending_department_update') ||
            session()->has('pending_department_delete') ||
            session()->has('pending_department_status_update')
        ) {

            $result = $this->departmentAIService
                ->process($request->command);

        }


        else {

            $result = [
                'success' => false,
                'message' => 'No pending action found.'
            ];

        }


    }


    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT COMMANDS
    |--------------------------------------------------------------------------
    */

    elseif (
        str_contains($command, 'department')
    ) {

        $result = $this->departmentAIService
            ->process($request->command);

    }


    /*
    |--------------------------------------------------------------------------
    | COMPANY COMMANDS
    |--------------------------------------------------------------------------
    */

    else {

        $result = $this->companyAIService
            ->process($request->command);

    }


    return response()->json($result);
}
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AI\CompanyAIService;
use App\Services\AI\DepartmentAIService;
use App\Services\AI\UserAIService;

class AIController extends Controller
{
    protected CompanyAIService $companyAIService;
    protected DepartmentAIService $departmentAIService;
    protected UserAIService $userAIService;

    public function __construct(
        CompanyAIService $companyAIService,
        DepartmentAIService $departmentAIService,
        UserAIService $userAIService
    ) {
        $this->companyAIService = $companyAIService;
        $this->departmentAIService = $departmentAIService;
        $this->userAIService = $userAIService;
    }

    public function execute(Request $request)
    {
        $request->validate([
            "command" => "required|string|max:1000",
        ]);

        $command = strtolower(trim($request->command));

        /*
        |--------------------------------------------------------------------------
        | CONFIRM ACTIONS
        |--------------------------------------------------------------------------
        */

        if ($command === "confirm") {
            // Company Pending Actions
            if (
                session()->has("pending_company_delete") ||
                session()->has("pending_company_update") ||
                session()->has("pending_company_activate") ||
                session()->has("pending_company_deactivate") ||
                session()->has("pending_company_restore")
            ) {
                $result = $this->companyAIService->process($request->command);
            }

            // Department Pending Actions
            elseif (
                session()->has("pending_department_update") ||
                session()->has("pending_department_delete") ||
                session()->has("pending_department_status_update")
            ) {
                $result = $this->departmentAIService->process(
                    $request->command
                );
            }

            // User Pending Actions
            elseif (
                session()->has("pending_user_create") ||
                session()->has("pending_user_update") ||
                session()->has("pending_user_delete") ||
                session()->has("pending_user_status") ||
                session()->has("pending_user_restore")
            ) {
                $result = $this->userAIService->process($request->command);
            } else {
                $result = [
                    "success" => false,
                    "message" => "No pending action found.",
                ];
            }
        }
        /*
        |--------------------------------------------------------------------------
        | USER COMMANDS
        |--------------------------------------------------------------------------
        */ elseif (
            str_contains($command, "user")
        ) {
            $result = $this->userAIService->process($request->command);
        }
        /*
        |--------------------------------------------------------------------------
        | DEPARTMENT COMMANDS
        |--------------------------------------------------------------------------
        */ elseif (
            str_contains($command, "department")
        ) {
            $result = $this->departmentAIService->process($request->command);
        }
        /*
        |--------------------------------------------------------------------------
        | COMPANY COMMANDS
        |--------------------------------------------------------------------------
        */ else {
            $result = $this->companyAIService->process($request->command);
        }

        return response()->json($result);
    }
}

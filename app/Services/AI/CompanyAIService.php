<?php

namespace App\Services\AI;

use App\Models\Company;
use App\Services\CompanyService;
use App\Notifications\CompanyActionNotification;

class CompanyAIService
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function process(string $command): array
    {
        $command = trim($command);

        /*
        |--------------------------------------------------------------------------
        | CREATE COMPANY
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), "create company")) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.create")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to create companies.",
                ];
            }

            $data = $this->parseCreateCompany($command);

            if (
                empty($data["name"]) ||
                empty($data["email"]) ||
                empty($data["phone"]) ||
                empty($data["address"])
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "Company name, email, phone and address are required.",
                ];
            }

            $existingCompany = $this->companyService->findByEmailWithTrashed(
                $data["email"]
            );

            if ($existingCompany) {
                if ($existingCompany->trashed()) {
                    return [
                        "success" => false,
                        "message" =>
                            "A deleted company already exists with this email. Please restore it or use another email.",
                    ];
                }

                return [
                    "success" => false,
                    "message" => "Company with email {$data["email"]} already exists.",
                ];
            }

            if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
                return [
                    "success" => false,
                    "message" => "Invalid email address.",
                ];
            }

            if (!preg_match('/^[0-9]{10,15}$/', $data["phone"])) {
                return [
                    "success" => false,
                    "message" =>
                        "Phone number must be between 10 and 15 digits.",
                ];
            }

            $company = $this->companyService->create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($company)
                ->withProperties([
                    "source" => "AI Assistant",
                    "command" => $command,
                ])
                ->log("Company created via AI Assistant");

            auth()
                ->user()
                ->notify(
                    new CompanyActionNotification(
                        "Company {$company->name} created via AI Assistant."
                    )
                );

            return [
                "success" => true,
                "message" => "Company {$company->name} has been created successfully.",
            ];
        }

        /*
|--------------------------------------------------------------------------
| UPDATE COMPANY REQUEST
|--------------------------------------------------------------------------
*/
        if (str_starts_with(strtolower($command), "update company")) {
            return $this->requestCompanyUpdateConfirmation($command);
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW ACTIVE COMPANIES
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), "show active companies")) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.view")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to view companies.",
                ];
            }

            $companies = $this->companyService->getActiveCompanies();

            if ($companies->isEmpty()) {
                return [
                    "success" => true,
                    "message" => "No active companies found.",
                ];
            }

            $message = "📋 ACTIVE COMPANIES\n\n";

            foreach ($companies as $index => $company) {
                $message .= $index + 1 . ") " . $company->name . "\n";
            }

            $message .= "\nTotal Active Companies: " . $companies->count();

            return [
                "success" => true,
                "message" => $message,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW INACTIVE COMPANIES
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), "show inactive companies")) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.view")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to view companies.",
                ];
            }

            $companies = $this->companyService->getInactiveCompanies();

            if ($companies->isEmpty()) {
                return [
                    "success" => true,
                    "message" => "No inactive companies found.",
                ];
            }

            $message = "📋 INACTIVE COMPANIES\n\n";

            foreach ($companies as $index => $company) {
                $message .= $index + 1 . ") " . $company->name . "\n";
            }

            $message .= "\nTotal Inactive Companies: " . $companies->count();

            return [
                "success" => true,
                "message" => $message,
            ];
        }

        /*
|--------------------------------------------------------------------------
| SHOW COMPANY STATISTICS
|--------------------------------------------------------------------------
*/
        if (
            str_contains(strtolower($command), "show company statistics") ||
            str_contains(strtolower($command), "company statistics")
        ) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.view")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to view companies.",
                ];
            }

            $stats = $this->companyService->getStatistics();

            $totalCompanies = $stats["total"];

            $activeCompanies = $stats["active"];

            $inactiveCompanies = $stats["inactive"];

            $percentage =
                $totalCompanies > 0
                    ? round(($activeCompanies / $totalCompanies) * 100, 2)
                    : 0;

            $message =
                "📊 COMPANY STATISTICS\n\n" .
                "Total Companies: {$totalCompanies}\n" .
                "Active Companies: {$activeCompanies}\n" .
                "Inactive Companies: {$inactiveCompanies}\n\n" .
                "Active Percentage: {$percentage}%";

            return [
                "success" => true,
                "message" => $message,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW COMPANY DETAILS
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), "show company")) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.view")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to view companies.",
                ];
            }

            preg_match('/show company\s+(.*)$/i', $command, $matches);

            $companyName = trim($matches[1] ?? "");

            $company = $this->companyService->findByName($companyName);

            if (!$company) {
                return [
                    "success" => false,
                    "message" => "Company {$companyName} not found.",
                ];
            }

            $company->load("departments");

            $status = $company->status ? "Active" : "Inactive";

            $totalDepartments = $company->departments->count();

            $totalUsers = $company->users()->count();

            $message =
                "🏢 COMPANY DETAILS\n\n" .
                "Name: {$company->name}\n" .
                "Email: {$company->email}\n" .
                "Phone: {$company->phone}\n" .
                "Address: {$company->address}\n" .
                "Status: {$status}\n" .
                "Total Departments: {$totalDepartments}\n" .
                "Total Users: {$totalUsers}";

            return [
                "success" => true,
                "message" => $message,
            ];
        }

        /*
|--------------------------------------------------------------------------
| DEACTIVATE COMPANY
|--------------------------------------------------------------------------
*/
        if (str_starts_with(strtolower($command), "deactivate company")) {
            return $this->requestCompanyDeactivateConfirmation($command);
        }

        /*
|--------------------------------------------------------------------------
| ACTIVATE COMPANY
|--------------------------------------------------------------------------
*/
        if (str_starts_with(strtolower($command), "activate company")) {
            return $this->requestCompanyActivateConfirmation($command);
        }

        /*
|--------------------------------------------------------------------------
| DELETE COMPANY REQUEST
|--------------------------------------------------------------------------
*/
        if (str_starts_with(strtolower($command), "delete company")) {
            return $this->requestCompanyDeleteConfirmation($command);
        }

        /*
|--------------------------------------------------------------------------
| RESTORE COMPANY REQUEST
|--------------------------------------------------------------------------
*/

        if (str_starts_with(strtolower($command), "restore company")) {
            return $this->requestCompanyRestoreConfirmation($command);
        }

        /*
|--------------------------------------------------------------------------
| CONFIRM ACTION
|--------------------------------------------------------------------------
*/
        if (strtolower($command) === "confirm") {
            /*
    |--------------------------------------------------------------------------
    | CONFIRM DELETE
    |--------------------------------------------------------------------------
    */
            if (session()->has("pending_company_delete")) {
                return $this->confirmCompanyDelete($command);
            }

            /*
|--------------------------------------------------------------------------
| CONFIRM RESTORE
|--------------------------------------------------------------------------
*/

            if (session()->has("pending_company_restore")) {
                return $this->confirmCompanyRestore($command);
            }
            /*
    |--------------------------------------------------------------------------
    | CONFIRM UPDATE
    |--------------------------------------------------------------------------
    */
            if (session()->has("pending_company_update")) {
                return $this->confirmCompanyUpdate($command);
            }

            /*
    |--------------------------------------------------------------------------
    | CONFIRM Deactivate
    |--------------------------------------------------------------------------
    */

            if (session()->has("pending_company_deactivate")) {
                return $this->confirmCompanyDeactivate($command);
            }

            if (session()->has("pending_company_activate")) {
                return $this->confirmCompanyActivate($command);
            }
            return [
                "success" => false,
                "message" => "No pending action found.",
            ];
        }

        /*
|--------------------------------------------------------------------------
| SHOW LATEST NOTIFICATIONS
|--------------------------------------------------------------------------
*/
        if (str_contains(strtolower($command), "show latest notifications")) {
            if (!auth()->check()) {
                return [
                    "success" => false,
                    "message" => "User not authenticated.",
                ];
            }

            $notifications = auth()
                ->user()
                ->notifications()
                ->latest()
                ->take(10)
                ->get();

            if ($notifications->isEmpty()) {
                return [
                    "success" => true,
                    "message" => "No notifications found.",
                ];
            }

            $message = "🔔 LATEST NOTIFICATIONS\n\n";

            foreach ($notifications as $index => $notification) {
                $message .=
                    $index +
                    1 .
                    ") " .
                    ($notification->data["message"] ?? "N/A") .
                    "\n";
            }

            $message .= "\nTotal Notifications: " . $notifications->count();

            return [
                "success" => true,
                "message" => $message,
            ];
        }
        /*
|--------------------------------------------------------------------------
| SEARCH COMPANY
|--------------------------------------------------------------------------
*/
        if (str_contains(strtolower($command), "search company")) {
            if (
                !auth()->check() ||
                !auth()
                    ->user()
                    ->can("companies.view")
            ) {
                return [
                    "success" => false,
                    "message" =>
                        "You do not have permission to view companies.",
                ];
            }

            preg_match('/search company\s+(.*)$/i', $command, $matches);

            $keyword = trim($matches[1] ?? "");

            if (empty($keyword)) {
                return [
                    "success" => false,
                    "message" => "Please enter a company name.",
                ];
            }

            $companies = $this->companyService->searchCompanies($keyword);

            if ($companies->isEmpty()) {
                return [
                    "success" => true,
                    "message" => "No matching companies found.",
                ];
            }

            $message = "🔍 SEARCH RESULTS\n\n";

            foreach ($companies as $index => $company) {
                $message .= $index + 1 . ") " . $company->name . "\n";
            }

            $message .= "\nTotal Results: " . $companies->count();

            return [
                "success" => true,
                "message" => $message,
            ];
        }

        return [
            "success" => false,
            "message" => "Sorry, I did not understand that command.",
        ];
    }

    private function requestCompanyUpdateConfirmation(string $command): array
    {
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to update companies.",
            ];
        }

        preg_match(
            "/update company\s+(.*?)\s+(email|phone|address)\s+/i",
            $command,
            $matches
        );

        $companyName = trim($matches[1] ?? "");

        if (empty($companyName)) {
            return [
                "success" => false,
                "message" => "Please provide company name.",
            ];
        }

        $company = $this->companyService->findByName($companyName);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company {$companyName} not found.",
            ];
        }

        $updateData = [];

        if (preg_match("/email\s+([^\s]+)/i", $command, $email)) {
            $updateData["email"] = $email[1];
        }

        if (preg_match("/phone\s+([0-9]+)/i", $command, $phone)) {
            $updateData["phone"] = $phone[1];
        }
        // Check same email
        if (
            isset($updateData["email"]) &&
            strtolower($company->email) === strtolower($updateData["email"])
        ) {
            return [
                "success" => false,
                "message" => "Company {$company->name} already has this email address. No changes required.",
            ];
        }

        if (
            isset($updateData["phone"]) &&
            !preg_match('/^[0-9]{10,15}$/', $updateData["phone"])
        ) {
            return [
                "success" => false,
                "message" => "Phone number must be between 10 and 15 digits.",
            ];
        }

        if (preg_match('/address\s+(.*)$/i', $command, $address)) {
            $updateData["address"] = trim($address[1]);
        }

        if (empty($updateData)) {
            return [
                "success" => false,
                "message" => "No update information found.",
            ];
        }

        if (
            isset($updateData["email"]) &&
            !filter_var($updateData["email"], FILTER_VALIDATE_EMAIL)
        ) {
            return [
                "success" => false,
                "message" => "Invalid email address.",
            ];
        }

        if (
            isset($updateData["email"]) &&
            $this->companyService->emailExistsExcept(
                $updateData["email"],
                $company->id
            )
        ) {
            return [
                "success" => false,
                "message" => "Email already exists.",
            ];
        }

        session([
            "pending_company_update" => [
                "company_id" => $company->id,
                "changes" => $updateData,
            ],
        ]);

        $message =
            "⚠️ UPDATE COMPANY CONFIRMATION\n\n" .
            "🏢 Company Details\n\n" .
            "Name: {$company->name}\n" .
            "Email: {$company->email}\n" .
            "Phone: {$company->phone}\n" .
            "Address: {$company->address}\n\n" .
            "Changes:\n";

        foreach ($updateData as $key => $value) {
            $message .= ucfirst($key) . " → " . $value . "\n";
        }

        $message .= "\nTo confirm update, type:\nconfirm";

        return [
            "success" => true,
            "message" => $message,
        ];
    }

    private function requestCompanyDeactivateConfirmation(
        string $command
    ): array {
        preg_match('/deactivate company\s+(.*)$/i', $command, $matches);

        $companyName = trim($matches[1] ?? "");

        $company = $this->companyService->findByNameWithDepartments(
            $companyName
        );

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company {$companyName} not found.",
            ];
        }

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" =>
                    "You do not have permission to deactivate companies.",
            ];
        }

        if (!$company->status) {
            return [
                "success" => false,
                "message" => "Company {$company->name} is already inactive.",
            ];
        }

        session([
            "pending_company_deactivate" => [
                "company_id" => $company->id,
            ],
        ]);

        return [
            "success" => true,
            "message" =>
                "⚠️ COMPANY DEACTIVATION CONFIRMATION\n\n" .
                "🏢 Company Details\n\n" .
                "Name: {$company->name}\n" .
                "Status: Active\n" .
                "Departments: " .
                $company->departments->count() .
                "\n" .
                "Active Departments: " .
                $company
                    ->departments()
                    ->where("status", 1)
                    ->count() .
                "\n\n" .
                "⚠️ Warning:\n" .
                "All active departments under this company will also be automatically deactivated.\n" .
                "Departments will not be automatically reactivated when the company is activated again.\n\n" .
                "To confirm deactivation, type:\n" .
                "confirm",
        ];
    }

    private function confirmCompanyDeactivate(string $command): array
    {
        $pending = session("pending_company_deactivate");

        if (!$pending) {
            return [
                "success" => false,
                "message" => "No pending deactivation request found.",
            ];
        }

        $company = $this->companyService->findById($pending["company_id"]);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" =>
                    "You do not have permission to deactivate companies.",
            ];
        }

        $this->companyService->toggleStatus($company);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                "source" => "AI Assistant",
                "command" => $command,
            ])
            ->log("Company deactivated via AI Assistant");

        auth()
            ->user()
            ->notify(
                new CompanyActionNotification(
                    "Company {$company->name} deactivated via AI Assistant."
                )
            );

        session()->forget("pending_company_deactivate");

        return [
            "success" => true,
            "message" => "Company {$company->name} has been deactivated successfully.",
        ];
    }
    private function requestCompanyActivateConfirmation(string $command): array
    {
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" =>
                    "You do not have permission to activate companies.",
            ];
        }

        preg_match('/activate company\s+(.*)$/i', $command, $matches);

        $companyName = trim($matches[1] ?? "");

        $company = $this->companyService->findByName($companyName);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company {$companyName} not found.",
            ];
        }

        if ($company->status) {
            return [
                "success" => false,
                "message" => "Company {$company->name} is already active.",
            ];
        }

        session([
            "pending_company_activate" => [
                "company_id" => $company->id,
            ],
        ]);

        return [
            "success" => true,
            "message" =>
                "⚠️ COMPANY ACTIVATION CONFIRMATION\n\n" .
                "🏢 Company: {$company->name}\n" .
                "Current Status: Inactive\n\n" .
                "⚠️ Note:\n" .
                "Departments that were automatically deactivated earlier will remain inactive.\n" .
                "You must activate them manually if required.\n\n" .
                "To confirm activation type:\n" .
                "confirm",
        ];
    }
    private function confirmCompanyActivate(string $command): array
    {
        $pending = session("pending_company_activate");

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" =>
                    "You do not have permission to activate companies.",
            ];
        }

        if (!$pending) {
            return [
                "success" => false,
                "message" => "No pending activation request found.",
            ];
        }

        $company = $this->companyService->findById($pending["company_id"]);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        $this->companyService->toggleStatus($company);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                "source" => "AI Assistant",
                "command" => $command,
            ])
            ->log("Company activated via AI Assistant");

        auth()
            ->user()
            ->notify(
                new CompanyActionNotification(
                    "Company {$company->name} activated via AI Assistant."
                )
            );

        session()->forget("pending_company_activate");

        return [
            "success" => true,
            "message" => "Company {$company->name} has been activated successfully.",
        ];
    }
    private function requestCompanyDeleteConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.delete")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to delete companies.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Extract Company Name
    |--------------------------------------------------------------------------
    */
        preg_match('/delete company\s+(.*)$/i', $command, $matches);

        $companyName = trim($matches[1] ?? "");

        if (empty($companyName)) {
            return [
                "success" => false,
                "message" => "Please enter a company name.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Find Company (Company Admin Scope)
    |--------------------------------------------------------------------------
    */
        $company = $this->companyService->findByNameWithDepartments(
            $companyName
        );

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company {$companyName} not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Delete Action
    |--------------------------------------------------------------------------
    */
        session([
            "pending_company_delete" => [
                "company_id" => $company->id,
                "company_name" => $company->name,
            ],
        ]);

        return [
            "success" => true,
            "message" =>
                "⚠️ DELETE COMPANY CONFIRMATION\n\n" .
                "Are you sure you want to delete this company?\n\n" .
                "🏢 Company Details\n\n" .
                "Name: {$company->name}\n" .
                "Email: {$company->email}\n" .
                "Phone: {$company->phone}\n" .
                "Address: {$company->address}\n" .
                "Status: " .
                ($company->status ? "Active" : "Inactive") .
                "\n" .
                "Departments: " .
                $company->departments->count() .
                "\n\n" .
                "⚠️ Warning:\n" .
                "Deleting this company will also delete all associated departments.\n" .
                "Users assigned to those departments may lose department association.\n" .
                "This action cannot be undone from the AI Assistant.\n\n" .
                "To confirm deletion, type:\n" .
                "confirm",
        ];
    }
    private function confirmCompanyUpdate(string $command): array
    {
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to update companies.",
            ];
        }

        $pendingUpdate = session("pending_company_update");

        if (!$pendingUpdate) {
            return [
                "success" => false,
                "message" => "No pending company update request found.",
            ];
        }

        $company = $this->companyService->findById(
            $pendingUpdate["company_id"]
        );

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        $changes = $pendingUpdate["changes"];

        $this->companyService->update($company, $changes);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                "source" => "AI Assistant",
                "command" => $command,
                "changes" => $changes,
            ])
            ->log("Company updated via AI Assistant");

        auth()
            ->user()
            ->notify(
                new CompanyActionNotification(
                    "Company {$company->name} updated via AI Assistant."
                )
            );

        session()->forget("pending_company_update");

        return [
            "success" => true,
            "message" => "Company {$company->name} has been updated successfully.",
        ];
    }
    private function confirmCompanyDelete(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.delete")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to delete companies.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Check Pending Delete Request
    |--------------------------------------------------------------------------
    */
        $pendingDelete = session("pending_company_delete");

        if (!$pendingDelete) {
            return [
                "success" => false,
                "message" => "No pending company deletion request found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */
        $company = $this->companyService->findById(
            $pendingDelete["company_id"]
        );

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Soft Delete Company
    |--------------------------------------------------------------------------
    */

        $companyName = $company->name;

        $userCount = $company->users()->count();

        if ($userCount > 0) {
            session()->forget("pending_company_delete");

            return [
                "success" => false,
                "message" => "Company {$company->name} contains {$userCount} users. Remove or reassign users before deleting the company.",
            ];
        }

        $this->companyService->delete($company);

        /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                "source" => "AI Assistant",
                "command" => $command,
            ])
            ->log("Company deleted via AI Assistant");

        /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */
        auth()
            ->user()
            ->notify(
                new CompanyActionNotification(
                    "Company {$companyName} deleted via AI Assistant."
                )
            );

        /*
    |--------------------------------------------------------------------------
    | Clear Session
    |--------------------------------------------------------------------------
    */
        session()->forget("pending_company_delete");

        return [
            "success" => true,
            "message" => "Company {$companyName} has been deleted successfully.",
        ];
    }

    private function requestCompanyRestoreConfirmation(string $command): array
    {
        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("companies.edit")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to restore companies.",
            ];
        }

        preg_match('/restore company\s+(.*)$/i', $command, $matches);

        $companyName = trim($matches[1] ?? "");

        if (empty($companyName)) {
            return [
                "success" => false,
                "message" => "Please enter company name.",
            ];
        }

        // Important: include deleted companies
        $company = $this->companyService->findDeletedByName($companyName);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Deleted company {$companyName} not found.",
            ];
        }

        if (!$company->trashed()) {
            return [
                "success" => false,
                "message" => "Company {$company->name} is already active.",
            ];
        }

        session([
            "pending_company_restore" => [
                "company_id" => $company->id,
            ],
        ]);

        return [
            "success" => true,
            "message" =>
                "⚠️ RESTORE COMPANY CONFIRMATION\n\n" .
                "🏢 Company Details\n\n" .
                "Name: {$company->name}\n" .
                "Email: {$company->email}\n" .
                "Status: Deleted\n\n" .
                "To confirm restoration, type:\n" .
                "confirm",
        ];
    }
    private function confirmCompanyRestore(string $command): array
    {
        $pending = session("pending_company_restore");

        if (!$pending) {
            return [
                "success" => false,
                "message" => "No pending restore request found.",
            ];
        }

        $company = $this->companyService->findWithTrashedById(
            $pending["company_id"]
        );

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        $company->restore();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                "source" => "AI Assistant",
                "command" => $command,
            ])
            ->log("Company restored via AI Assistant");

        auth()
            ->user()
            ->notify(
                new CompanyActionNotification(
                    "Company {$company->name} restored via AI Assistant."
                )
            );

        session()->forget("pending_company_restore");

        return [
            "success" => true,
            "message" => "Company {$company->name} has been restored successfully.",
        ];
    }
    private function parseCreateCompany(string $command): array
    {
        preg_match(
            "/company\s+named\s+(.*?)\s+with\s+email\s+([^\s]+)/i",
            $command,
            $nameEmail
        );

        preg_match("/phone\s+([0-9]+)/i", $command, $phone);

        preg_match('/address\s+(.*)$/i', $command, $address);

        return [
            "name" => trim($nameEmail[1] ?? ""),
            "email" => trim($nameEmail[2] ?? ""),
            "phone" => trim($phone[1] ?? ""),
            "address" => trim($address[1] ?? ""),
            "status" => 1,
        ];
    }
}

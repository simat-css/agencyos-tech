<?php

namespace App\Services;

use App\Models\Company;
use App\Notifications\CompanyActionNotification;
use Illuminate\Support\Facades\Auth;

class AICommandService
{
    protected CompanyService $companyService;

    public function __construct(
        CompanyService $companyService
    ) {
        $this->companyService = $companyService;
    }

    private function companyQuery()
{
    $query = Company::query();

    if (auth()->user()->hasRole('Company Admin')) {

        $query->where(
            'id',
            auth()->user()->company_id
        );
    }

    return $query;
}

    public function process(string $command): array
    {
        $command = trim($command);

        /*
        |--------------------------------------------------------------------------
        | CREATE COMPANY
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), 'create company')) {

            if (
                !auth()->check() ||
                !auth()->user()->can('companies.create')
            ) {
                return [
                    'success' => false,
                    'message' =>
                        'You do not have permission to create companies.'
                ];
            }

            $data = $this->parseCreateCompany($command);

            if (
                empty($data['name']) ||
                empty($data['email']) ||
                empty($data['phone']) ||
                empty($data['address'])
            ) {
                return [
                    'success' => false,
                    'message' =>
                        'Company name, email, phone and address are required.'
                ];
            }

            if (
                Company::where('email', $data['email'])->exists()
            ) {
                return [
                    'success' => false,
                    'message' =>
                        "Company with email {$data['email']} already exists."
                ];
            }

            $company = $this->companyService->create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($company)
                ->withProperties([
                    'source' => 'AI Assistant',
                    'command' => $command
                ])
                ->log('Company created via AI Assistant');

            auth()->user()->notify(
                new CompanyActionNotification(
                    "Company {$company->name} created via AI Assistant."
                )
            );

            return [
                'success' => true,
                'message' =>
                    "Company {$company->name} has been created successfully."
            ];
        }

       /*
|--------------------------------------------------------------------------
| UPDATE COMPANY REQUEST
|--------------------------------------------------------------------------
*/
if (str_starts_with(strtolower($command), 'update company')) {

    return $this->requestCompanyUpdateConfirmation($command);
}

        /*
        |--------------------------------------------------------------------------
        | SHOW ACTIVE COMPANIES
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), 'show active companies')) {

            if (
                !auth()->check() ||
                !auth()->user()->can('companies.view')
            ) {
                return [
                    'success' => false,
                    'message' =>
                        'You do not have permission to view companies.'
                ];
            }

            $companies = $this->companyQuery()
    ->where('status', 1)
    ->orderBy('name')
    ->get();

            if ($companies->isEmpty()) {

                return [
                    'success' => true,
                    'message' => 'No active companies found.'
                ];
            }

            $message = "📋 ACTIVE COMPANIES\n\n";

            foreach ($companies as $index => $company) {

                $message .= ($index + 1)
                    . ") "
                    . $company->name
                    . "\n";
            }

            $message .= "\nTotal Active Companies: "
                . $companies->count();

            return [
                'success' => true,
                'message' => $message
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW INACTIVE COMPANIES
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), 'show inactive companies')) {

            if (
                !auth()->check() ||
                !auth()->user()->can('companies.view')
            ) {
                return [
                    'success' => false,
                    'message' =>
                        'You do not have permission to view companies.'
                ];
            }

            $companies = $this->companyQuery()->where('status', 0)
                ->orderBy('name')
                ->get();

            if ($companies->isEmpty()) {

                return [
                    'success' => true,
                    'message' =>
                        'No inactive companies found.'
                ];
            }

            $message = "📋 INACTIVE COMPANIES\n\n";

            foreach ($companies as $index => $company) {

                $message .= ($index + 1)
                    . ") "
                    . $company->name
                    . "\n";
            }

            $message .= "\nTotal Inactive Companies: "
                . $companies->count();

            return [
                'success' => true,
                'message' => $message
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW COMPANY DETAILS
        |--------------------------------------------------------------------------
        */
        if (str_contains(strtolower($command), 'show company')) {

            if (
                !auth()->check() ||
                !auth()->user()->can('companies.view')
            ) {
                return [
                    'success' => false,
                    'message' =>
                        'You do not have permission to view companies.'
                ];
            }

            preg_match(
                '/show company\s+(.*)$/i',
                $command,
                $matches
            );

            $companyName = trim($matches[1] ?? '');

            $company = $this->companyQuery()->with('departments')
                ->where('name', $companyName)
                ->first();

            if (!$company) {

                return [
                    'success' => false,
                    'message' =>
                        "Company {$companyName} not found."
                ];
            }

            $status = $company->status
                ? 'Active'
                : 'Inactive';

            $message =
                "🏢 COMPANY DETAILS\n\n" .
                "Name: {$company->name}\n" .
                "Email: {$company->email}\n" .
                "Phone: {$company->phone}\n" .
                "Address: {$company->address}\n" .
                "Status: {$status}\n" .
                "Departments: " .
                $company->departments->count();

            return [
                'success' => true,
                'message' => $message
            ];
        }

       /*
|--------------------------------------------------------------------------
| DEACTIVATE COMPANY
|--------------------------------------------------------------------------
*/
if (str_starts_with(strtolower($command), 'deactivate company')) {

    return $this->changeCompanyStatus(
        $command,
        false
    );
}


/*
|--------------------------------------------------------------------------
| ACTIVATE COMPANY
|--------------------------------------------------------------------------
*/
if (str_starts_with(strtolower($command), 'activate company')) {

    return $this->changeCompanyStatus(
        $command,
        true
    );
}


/*
|--------------------------------------------------------------------------
| DELETE COMPANY REQUEST
|--------------------------------------------------------------------------
*/
if (str_starts_with(strtolower($command), 'delete company')) {

    return $this->requestCompanyDeleteConfirmation($command);
}

/*
|--------------------------------------------------------------------------
| CONFIRM ACTION
|--------------------------------------------------------------------------
*/
if (strtolower($command) === 'confirm') {


    /*
    |--------------------------------------------------------------------------
    | CONFIRM DELETE
    |--------------------------------------------------------------------------
    */
    if (session()->has('pending_company_delete')) {

        return $this->confirmCompanyDelete($command);

    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRM UPDATE
    |--------------------------------------------------------------------------
    */
    if (session()->has('pending_company_update')) {

        return $this->confirmCompanyUpdate($command);

    }


    return [
        'success' => false,
        'message' => 'No pending action found.'
    ];
}
/*
|--------------------------------------------------------------------------
| CONFIRM DELETE COMPANY WITH UPDATE SUPPORT
|--------------------------------------------------------------------------
*/
if (strtolower($command) === 'confirm') {


    if (session()->has('pending_company_delete')) {

        return $this->confirmCompanyDelete($command);

    }


    if (session()->has('pending_company_update')) {

        return $this->confirmCompanyUpdate($command);

    }


    return [
        'success' => false,
        'message' =>
            'No pending action found.'
    ];
}

        /*
|--------------------------------------------------------------------------
| SHOW LATEST NOTIFICATIONS
|--------------------------------------------------------------------------
*/
if (str_contains(strtolower($command), 'show latest notifications')) {

    if (!auth()->check()) {

        return [
            'success' => false,
            'message' => 'User not authenticated.'
        ];
    }

    $notifications = auth()->user()
        ->notifications()
        ->latest()
        ->take(10)
        ->get();

    if ($notifications->isEmpty()) {

        return [
            'success' => true,
            'message' => 'No notifications found.'
        ];
    }

    $message = "🔔 LATEST NOTIFICATIONS\n\n";

    foreach ($notifications as $index => $notification) {

        $message .= ($index + 1)
            . ") "
            . ($notification->data['message'] ?? 'N/A')
            . "\n";
    }

    $message .= "\nTotal Notifications: "
        . $notifications->count();

    return [
        'success' => true,
        'message' => $message
    ];
}
/*
|--------------------------------------------------------------------------
| SHOW COMPANY STATISTICS
|--------------------------------------------------------------------------
*/
if (str_contains(strtolower($command), 'show company statistics') || str_contains(strtolower($command), 'company statistics'))
{
    if (
        !auth()->check() ||
        !auth()->user()->can('companies.view')
    ) {
        return [
            'success' => false,
            'message' => 'You do not have permission to view companies.'
        ];
    }

    $totalCompanies = $this->companyQuery()->count();

$activeCompanies = $this->companyQuery()
    ->where('status',1)
    ->count();

$inactiveCompanies = $this->companyQuery()
    ->where('status',0)
    ->count();

    $percentage = $totalCompanies > 0
        ? round(
            ($activeCompanies / $totalCompanies) * 100,
            2
        )
        : 0;

    $message =
        "📊 COMPANY STATISTICS\n\n" .
        "Total Companies: {$totalCompanies}\n" .
        "Active Companies: {$activeCompanies}\n" .
        "Inactive Companies: {$inactiveCompanies}\n\n" .
        "Active Percentage: {$percentage}%";

    return [
        'success' => true,
        'message' => $message
    ];
}
/*
|--------------------------------------------------------------------------
| SEARCH COMPANY
|--------------------------------------------------------------------------
*/
if (str_contains(strtolower($command), 'search company')) {

    if (
        !auth()->check() ||
        !auth()->user()->can('companies.view')
    ) {
        return [
            'success' => false,
            'message' => 'You do not have permission to view companies.'
        ];
    }

    preg_match(
        '/search company\s+(.*)$/i',
        $command,
        $matches
    );

    $keyword = trim($matches[1] ?? '');

    if (empty($keyword)) {

        return [
            'success' => false,
            'message' => 'Please enter a company name.'
        ];
    }

  $companies = $this->companyQuery()
    ->where(
        'name',
        'like',
        "%{$keyword}%"
    )
    ->get();

    if ($companies->isEmpty()) {

        return [
            'success' => true,
            'message' => 'No matching companies found.'
        ];
    }

    $message = "🔍 SEARCH RESULTS\n\n";

    foreach ($companies as $index => $company) {

        $message .= ($index + 1)
            . ") "
            . $company->name
            . "\n";
    }

    $message .= "\nTotal Results: "
        . $companies->count();

    return [
        'success' => true,
        'message' => $message
    ];
}

        return [
            'success' => false,
            'message' =>
                'Sorry, I did not understand that command.'
        ];
    }
    
private function requestCompanyUpdateConfirmation(
    string $command
): array {

    if (
        !auth()->check() ||
        !auth()->user()->can('companies.edit')
    ) {
        return [
            'success' => false,
            'message' =>
                'You do not have permission to update companies.'
        ];
    }


    preg_match(
        '/update company\s+(.*?)\s+(email|phone|address)\s+/i',
        $command,
        $matches
    );


    $companyName = trim($matches[1] ?? '');


    if (empty($companyName)) {

        return [
            'success' => false,
            'message' =>
                'Please provide company name.'
        ];
    }


    $company = $this->companyQuery()
        ->where(
            'name',
            'like',
            "%{$companyName}%"
        )
        ->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                "Company {$companyName} not found."
        ];
    }


    $updateData = [];


    if (
        preg_match(
            '/email\s+([^\s]+)/i',
            $command,
            $email
        )
    ) {
        $updateData['email'] = $email[1];
    }


    if (
        preg_match(
            '/phone\s+([0-9]+)/i',
            $command,
            $phone
        )
    ) {
        $updateData['phone'] = $phone[1];
    }


    if (
        preg_match(
            '/address\s+(.*)$/i',
            $command,
            $address
        )
    ) {
        $updateData['address'] = trim($address[1]);
    }


    if (empty($updateData)) {

        return [
            'success' => false,
            'message' =>
                'No update information found.'
        ];
    }


    session([
        'pending_company_update' => [
            'company_id' => $company->id,
            'changes' => $updateData
        ]
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

        $message .=
            ucfirst($key)
            . " → "
            . $value
            . "\n";
    }


    $message .=
        "\nTo confirm update, type:\nconfirm";


    return [
        'success' => true,
        'message' => $message
    ];
}
private function updateCompanyFromAI(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */
    if (
        !auth()->check() ||
        !auth()->user()->can('companies.edit')
    ) {
        return [
            'success' => false,
            'message' =>
                'You do not have permission to update companies.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Company Name
    |--------------------------------------------------------------------------
    */
    preg_match(
        '/update company\s+(.*?)\s+(email|phone|address|name)\s+/i',
        $command,
        $matches
    );


    $companyName = trim($matches[1] ?? '');


    if (empty($companyName)) {

        return [
            'success' => false,
            'message' =>
                'Please provide company name.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company (Company Scope)
    |--------------------------------------------------------------------------
    */
    $company = $this->companyQuery()
        ->where(
            'name',
            'like',
            "%{$companyName}%"
        )
        ->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                "Company {$companyName} not found."
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Update Data
    |--------------------------------------------------------------------------
    */
    $updateData = [];


    if (
        preg_match(
            '/email\s+([^\s]+)/i',
            $command,
            $email
        )
    ) {

        $updateData['email'] = $email[1];
    }


    if (
        preg_match(
            '/phone\s+([0-9]+)/i',
            $command,
            $phone
        )
    ) {

        $updateData['phone'] = $phone[1];
    }


    if (
        preg_match(
            '/address\s+(.*)$/i',
            $command,
            $address
        )
    ) {

        $updateData['address'] = trim($address[1]);
    }



    if (empty($updateData)) {

        return [
            'success' => false,
            'message' =>
                'No update information found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Update Company
    |--------------------------------------------------------------------------
    */
    $company->update($updateData);



    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    activity()
        ->causedBy(auth()->user())
        ->performedOn($company)
        ->withProperties([
            'source' => 'AI Assistant',
            'command' => $command,
            'changes' => $updateData
        ])
        ->log(
            'Company updated via AI Assistant'
        );



    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */
    auth()->user()->notify(
        new CompanyActionNotification(
            "Company {$company->name} updated via AI Assistant."
        )
    );


    return [
        'success' => true,
        'message' =>
            "Company {$company->name} has been updated successfully."
    ];
}    
private function requestCompanyDeleteConfirmation(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */
    if (
        !auth()->check() ||
        !auth()->user()->can('companies.delete')
    ) {
        return [
            'success' => false,
            'message' =>
                'You do not have permission to delete companies.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Company Name
    |--------------------------------------------------------------------------
    */
    preg_match(
        '/delete company\s+(.*)$/i',
        $command,
        $matches
    );

    $companyName = trim($matches[1] ?? '');


    if (empty($companyName)) {

        return [
            'success' => false,
            'message' =>
                'Please enter a company name.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company (Company Admin Scope)
    |--------------------------------------------------------------------------
    */
$company = $this->companyQuery()
    ->with('departments')
    ->where('name','like',"%{$companyName}%")
    ->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                "Company {$companyName} not found."
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Store Pending Delete Action
    |--------------------------------------------------------------------------
    */
    session([
        'pending_company_delete' => [
            'company_id' => $company->id,
            'company_name' => $company->name
        ]
    ]);


    return [
        'success' => true,
        'message' =>
            "⚠️ DELETE COMPANY CONFIRMATION\n\n" .
"Are you sure you want to delete this company?\n\n" .
"🏢 Company Details\n\n" .
"Name: {$company->name}\n" .
"Email: {$company->email}\n" .
"Phone: {$company->phone}\n" .
"Address: {$company->address}\n" .
"Status: " . ($company->status ? 'Active' : 'Inactive') . "\n" .
"Departments: " . $company->departments->count() . "\n\n" .
"⚠️ Warning:\n" .
"This action will soft delete the company.\n" .
"The company can be restored later.\n\n" .
"To confirm deletion, type:\n" .
"confirm delete company {$company->name}"
    ];
}
private function confirmCompanyUpdate(
    string $command
): array {


    if (
        !auth()->check() ||
        !auth()->user()->can('companies.edit')
    ) {
        return [
            'success' => false,
            'message' =>
                'You do not have permission to update companies.'
        ];
    }


    $pendingUpdate = session(
        'pending_company_update'
    );


    if (!$pendingUpdate) {

        return [
            'success' => false,
            'message' =>
                'No pending company update request found.'
        ];
    }


    $company = $this->companyQuery()
        ->where(
            'id',
            $pendingUpdate['company_id']
        )
        ->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];
    }


    $changes = $pendingUpdate['changes'];


    $company->update($changes);



    activity()
        ->causedBy(auth()->user())
        ->performedOn($company)
        ->withProperties([
            'source' => 'AI Assistant',
            'command' => $command,
            'changes' => $changes
        ])
        ->log(
            'Company updated via AI Assistant'
        );



    auth()->user()->notify(
        new CompanyActionNotification(
            "Company {$company->name} updated via AI Assistant."
        )
    );



    session()->forget(
        'pending_company_update'
    );


    return [
        'success' => true,
        'message' =>
            "Company {$company->name} has been updated successfully."
    ];
}
private function confirmCompanyDelete(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */
    if (
        !auth()->check() ||
        !auth()->user()->can('companies.delete')
    ) {
        return [
            'success' => false,
            'message' =>
                'You do not have permission to delete companies.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Pending Delete Request
    |--------------------------------------------------------------------------
    */
    $pendingDelete = session(
        'pending_company_delete'
    );


    if (!$pendingDelete) {

        return [
            'success' => false,
            'message' =>
                'No pending company deletion request found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */
    $company = $this->companyQuery()
        ->where(
            'id',
            $pendingDelete['company_id']
        )
        ->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Soft Delete Company
    |--------------------------------------------------------------------------
    */
    $companyName = $company->name;

    $company->delete();


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    activity()
        ->causedBy(auth()->user())
        ->performedOn($company)
        ->withProperties([
            'source' => 'AI Assistant',
            'command' => $command
        ])
        ->log('Company deleted via AI Assistant');


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */
    auth()->user()->notify(
        new CompanyActionNotification(
            "Company {$companyName} deleted via AI Assistant."
        )
    );


    /*
    |--------------------------------------------------------------------------
    | Clear Session
    |--------------------------------------------------------------------------
    */
    session()->forget(
        'pending_company_delete'
    );


    return [
        'success' => true,
        'message' =>
            "Company {$companyName} has been deleted successfully."
    ];
}

    private function changeCompanyStatus(
        string $command,
        bool $activate
    ): array {

        if (
            !auth()->check() ||
            !auth()->user()->can('companies.edit')
        ) {
            return [
                'success' => false,
                'message' =>
                    'You do not have permission to update companies.'
            ];
        }

        preg_match(
            '/(activate|deactivate)\s+company\s+(.*)$/i',
            $command,
            $matches
        );

        $companyName = trim($matches[2] ?? '');

      $company = $this->companyQuery()
    ->where(
        'name',
        $companyName
    )
    ->first();

        if (!$company) {

            return [
                'success' => false,
                'message' =>
                    "Company {$companyName} not found."
            ];
        }

        if ($company->status == $activate) {

            return [
                'success' => false,
                'message' =>
                    $activate
                    ? "Company {$company->name} is already active."
                    : "Company {$company->name} is already inactive."
            ];
        }

        $this->companyService->toggleStatus($company);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($company)
            ->withProperties([
                'source' => 'AI Assistant',
                'command' => $command
            ])
            ->log(
                $activate
                ? 'Company activated via AI Assistant'
                : 'Company deactivated via AI Assistant'
            );

        auth()->user()->notify(
            new CompanyActionNotification(
                $activate
                ? "Company {$company->name} activated via AI Assistant."
                : "Company {$company->name} deactivated via AI Assistant."
            )
        );

        return [
            'success' => true,
            'message' =>
                $activate
                ? "Company {$company->name} has been activated successfully."
                : "Company {$company->name} has been deactivated successfully."
        ];
    }

    private function parseCreateCompany(
        string $command
    ): array {

        preg_match(
            '/company\s+named\s+(.*?)\s+with\s+email\s+([^\s]+)/i',
            $command,
            $nameEmail
        );

        preg_match(
            '/phone\s+([0-9]+)/i',
            $command,
            $phone
        );

        preg_match(
            '/address\s+(.*)$/i',
            $command,
            $address
        );

        return [
            'name'    => trim($nameEmail[1] ?? ''),
            'email'   => trim($nameEmail[2] ?? ''),
            'phone'   => trim($phone[1] ?? ''),
            'address' => trim($address[1] ?? ''),
            'status'  => 1,
        ];
    }
}
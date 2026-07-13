<?php

namespace App\Services\AI;

use App\Services\DepartmentService;
use App\Models\Company;
use App\Models\Department;
use App\Notifications\DepartmentActionNotification;

class DepartmentAIService
{
    protected DepartmentService $departmentService;

    public function __construct(
        DepartmentService $departmentService
    ) {
        $this->departmentService = $departmentService;
    }

    public function process(
        string $command
    ): array {

        $command = trim($command);

        if (
            str_contains(
                strtolower($command),
                'create department'
            )
        ) {
            return $this->handleCreateDepartment(
                $command
            );
        }

        if (
    str_starts_with(
        strtolower($command),
        'update department'
    )
) {
    return $this->requestDepartmentUpdateConfirmation(
        $command
    );
}

//delete department
if (
    str_starts_with(
        strtolower($command),
        'delete department'
    )
) {
    return $this->requestDepartmentDeleteConfirmation(
        $command
    );
}

//activate department

if (
    str_starts_with(
        strtolower($command),
        'activate department'
    )
) {

    return $this->requestDepartmentStatusConfirmation(
        $command,
        1
    );

}

//deactivate department

if (
    str_starts_with(
        strtolower($command),
        'deactivate department'
    )
) {

    return $this->requestDepartmentStatusConfirmation(
        $command,
        0
    );

}

//show department

if (
    str_starts_with(
        strtolower($command),
        'show department'
    )
) {

    return $this->showDepartmentDetails(
        $command
    );

}

//search department

if (
    str_starts_with(
        strtolower($command),
        'search department'
    )
) {

    return $this->searchDepartments(
        $command
    );

}
//list departments

if (
    str_starts_with(strtolower($command), 'list department')
    ||
    str_starts_with(strtolower($command), 'list departments')
) {

    return $this->listDepartments(
        $command
    );

}

//confirm
if (
    strtolower($command) === 'confirm'
) {

    if (
        session()->has(
            'pending_department_update'
        )
    ) {
        return $this->confirmDepartmentUpdate(
            $command
        );
    }

    if (
        session()->has(
            'pending_department_delete'
        )
    ) {
        return $this->confirmDepartmentDelete(
            $command
        );
    }

    if (
    session()->has(
        'pending_department_status_update'
    )
) {

    return $this->confirmDepartmentStatusUpdate(
        $command
    );

}

    return [
        'success' => false,
        'message' =>
            'No pending department action found.'
    ];
}

        return [
            'success' => false,
            'message' =>
                'Department command not recognized.'
        ];
    }

    private function handleCreateDepartment(
        string $command
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Permission Validation
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->check()
            ||
            !auth()->user()->can(
                'departments.create'
            )
        ) {

            return [
                'success' => false,
                'message' =>
                    'You do not have permission to create departments.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Parse Command
        |--------------------------------------------------------------------------
        */

        $data =
            $this->parseCreateDepartment(
                $command
            );

        /*
        |--------------------------------------------------------------------------
        | Mandatory Fields Validation
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['name'])
            ||
            empty($data['company_name'])
            ||
            empty($data['code'])
        ) {

            return [
                'success' => false,
                'message' =>
                    'Department name, company and code are required.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find Company
        |--------------------------------------------------------------------------
        */

        $company = Company::where(
            'name',
            'like',
            '%' .
            $data['company_name']
            . '%'
        )->first();

        if (!$company) {

            return [
                'success' => false,
                'message' =>
                    'Company not found.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Active Company Validation
        |--------------------------------------------------------------------------
        */

        if (!$company->status) {

            return [
                'success' => false,
                'message' =>
                    'Cannot create department under inactive company.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Company Admin Restriction
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->hasRole(
                'Company Admin'
            )
            &&
            auth()->user()->company_id
            !=
            $company->id
        ) {

            return [
                'success' => false,
                'message' =>
                    'You can only manage departments of your own company.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Department Code Validation
        |--------------------------------------------------------------------------
        */

        if (
            $this->departmentService
                ->codeExists(
                    $data['code'],
                    $company->id
                )
        ) {

            return [
                'success' => false,
                'message' =>
                    'Department code already exists.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Department Name Validation
        |--------------------------------------------------------------------------
        */

        $existingDepartment =
            $this->departmentService
                ->findByName(
                    $data['name'],
                    $company->id
                );

        if ($existingDepartment) {

            return [
                'success' => false,
                'message' =>
                    'Department name already exists in this company.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Service Execution
        |--------------------------------------------------------------------------
        */

        $departmentData = [

            'company_id' => $company->id,

            'name' => $data['name'],

            'code' => $data['code'],

            'description' =>
                $data['description'] ?? null,

            'status' => 1,

        ];

        $department =
            $this->departmentService->create(
                $departmentData
            );

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        activity()
            ->causedBy(auth()->user())
            ->performedOn($department)
            ->withProperties([

                'user_id' =>
                    auth()->id(),

                'module' =>
                    'Departments',

                'action' =>
                    'Create',

                'old_values' =>
                    null,

                'new_values' =>
                    $department->toArray(),

                'ip_address' =>
                    request()->ip(),

                'browser' =>
                    request()->userAgent(),

                'source' =>
                    'AI Assistant',

                'command' =>
                    $command,

            ])
            ->log(
                'Department created via AI Assistant'
            );

        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        auth()->user()->notify(

            new DepartmentActionNotification(

                "Department {$department->name} created successfully."

            )

        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [

            'success' => true,

            'message' =>

                "Department {$department->name} has been created successfully."

        ];
    }

    private function requestDepartmentUpdateConfirmation(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !$this->hasDepartmentPermission(
            'departments.edit'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission to update departments.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Parse Department + Company
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseUpdateDepartmentCommand(
            $command
        );

    $departmentName =
        $data['department_name'];

    $companyName =
        $data['company_name'];

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        !$departmentName
        ||
        !$companyName
    ) {

        return [
            'success' => false,
            'message' =>
                'Department name and company name are required.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company = Company::where(
        'name',
        'like',
        '%'.$companyName.'%'
    )->first();

    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findByName(
                $departmentName,
                $company->id
            );

    if (!$department) {

        return [
            'success' => false,
            'message' =>
                "Department {$departmentName} not found."
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Company Access Validation
    |--------------------------------------------------------------------------
    */

    if (
        !$this->checkDepartmentCompanyAccess(
            $department
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You cannot access another company department.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Extract Changes
    |--------------------------------------------------------------------------
    */

    $changes = [];

    if (
        preg_match(
            '/name\s+(.*?)(?:\s+code|\s+description|$)/i',
            $command,
            $name
        )
    ) {

        $changes['name'] =
            trim($name[1]);

    }

    if (
        preg_match(
            '/code\s+([a-zA-Z0-9_-]+)/i',
            $command,
            $code
        )
    ) {

        $changes['code'] =
            trim($code[1]);

    }

    if (
        preg_match(
            '/description\s+(.*)$/i',
            $command,
            $description
        )
    ) {

        $changes['description'] =
            trim($description[1]);

    }

    /*
    |--------------------------------------------------------------------------
    | Validate Changes
    |--------------------------------------------------------------------------
    */

    if (empty($changes)) {

        return [
            'success' => false,
            'message' =>
                'No update information found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Store Pending Request
    |--------------------------------------------------------------------------
    */

    session([

        'pending_department_update' => [

            'department_id' =>
                $department->id,

            'changes' =>
                $changes

        ]

    ]);

    /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

    $message =
        "⚠️ UPDATE DEPARTMENT CONFIRMATION\n\n".
        "Department: {$department->name}\n".
        "Company: ".($department->company->name ?? 'N/A')."\n".
        "Department Code: ".($department->code ?? 'N/A')."\n\n".
        "Changes:\n";

    foreach (
        $changes as $key => $value
    ) {

        $message .=
            ucfirst($key)
            ." → "
            .$value
            ."\n";

    }

    $message .=
        "\nType confirm to continue.";

    return [

        'success' => true,

        'message' => $message

    ];

}
private function confirmDepartmentUpdate(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !auth()->check()
        ||
        !auth()->user()->can(
            'departments.edit'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Pending Request Validation
    |--------------------------------------------------------------------------
    */

    $pending = session(
        'pending_department_update'
    );

    if (!$pending) {

        return [
            'success' => false,
            'message' =>
                'No pending department update request found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findById(
                $pending['department_id']
            );

    if (!$department) {

        session()->forget(
            'pending_department_update'
        );

        return [
            'success' => false,
            'message' =>
                'Department not found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole(
            'Company Admin'
        )
        &&
        $department->company_id
        !=
        auth()->user()->company_id
    ) {

        return [
            'success' => false,
            'message' =>
                'You cannot update another company department.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Department Name Validation
    |--------------------------------------------------------------------------
    */

    if (
        isset(
            $pending['changes']['name']
        )
    ) {

        $existingDepartment =
            $this->departmentService
                ->findByName(
                    $pending['changes']['name'],
                    $department->company_id
                );

        if ($existingDepartment && $existingDepartment->id != $department->id) {

    session()->forget(
        'pending_department_update'
    );

    return [
        'success' => false,
        'message' => 'Department name already exists.'
    ];

}

    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Department Code Validation
    |--------------------------------------------------------------------------
    */

    if (
        isset(
            $pending['changes']['code']
        )
    ) {

       if (
    $this->departmentService
        ->codeExists(
            $pending['changes']['code'],
            $department->company_id,
            $department->id
        )
) {

    session()->forget(
        'pending_department_update'
    );

    return [
        'success' => false,
        'message' => 'Department code already exists.'
    ];

}

    }

    /*
    |--------------------------------------------------------------------------
    | Store Old Values
    |--------------------------------------------------------------------------
    */

    $oldValues =
        $department->toArray();

    /*
    |--------------------------------------------------------------------------
    | Update Department
    |--------------------------------------------------------------------------
    */

    $updatedDepartment =
        $this->departmentService
            ->update(
                $department,
                $pending['changes']
            );

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    activity()
        ->causedBy(auth()->user())
        ->performedOn($updatedDepartment)
        ->withProperties([

            'user_id' =>
                auth()->id(),

            'module' =>
                'Departments',

            'action' =>
                'Update',

            'old_values' =>
                $oldValues,

            'new_values' =>
    $updatedDepartment->fresh()->toArray(),

            'ip_address' =>
                request()->ip(),

            'browser' =>
                request()->userAgent(),

            'source' =>
                'AI Assistant',

            'command' =>
                $command,

        ])
        ->log(
            'Department updated via AI Assistant'
        );

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    auth()->user()->notify(

        new DepartmentActionNotification(

            "Department {$updatedDepartment->name} updated successfully."

        )

    );

    /*
    |--------------------------------------------------------------------------
    | Clear Pending Session
    |--------------------------------------------------------------------------
    */

    session()->forget(
        'pending_department_update'
    );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>

            "Department {$updatedDepartment->name} has been updated successfully."

    ];

}

private function requestDepartmentStatusConfirmation(
    string $command,
    int $status
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !auth()->check()
        ||
        !auth()->user()->can(
            'departments.edit'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission to change department status.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Parse Department + Company
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseDepartmentAndCompany(
            $command
        );


    $departmentName =
        $data['department_name'];


    $companyName =
        $data['company_name'];


    if (!$departmentName || !$companyName) {

        return [
            'success' => false,
            'message' =>
                'Department name and company name are required.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company = Company::where(
        'name',
        'like',
        '%'.$companyName.'%'
    )->first();


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole('Company Admin')
        &&
        auth()->user()->company_id != $company->id
    ) {

        return [
            'success' => false,
            'message' =>
                'You can only manage your own company departments.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findByName(
                $departmentName,
                $company->id
            );


    if (!$department) {

        return [
            'success' => false,
            'message' =>
                "Department {$departmentName} not found."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Check Current Status
    |--------------------------------------------------------------------------
    */

    if (
        $department->status == $status
    ) {

        return [
            'success' => false,
            'message' =>
                $status
                ?
                'Department is already active.'
                :
                'Department is already inactive.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Store Pending Action
    |--------------------------------------------------------------------------
    */

    session([

        'pending_department_status_update' => [

            'department_id' =>
                $department->id,

            'status' =>
                $status

        ]

    ]);


    $newStatus =
        $status
        ?
        'Active'
        :
        'Inactive';


    return [

        'success' => true,

        'message' =>

            "⚠️ DEPARTMENT STATUS CONFIRMATION\n\n".
            "Department: {$department->name}\n".
            "Company: ".($department->company->name ?? 'N/A')."\n".
            "New Status: {$newStatus}\n\n".
            "Type confirm to continue."

    ];

}

//delete confirmation
private function requestDepartmentDeleteConfirmation(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !$this->hasDepartmentPermission(
            'departments.delete'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission to delete departments.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Parse Department + Company
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseDepartmentAndCompany(
            $command
        );

    $departmentName =
        $data['department_name'];

    $companyName =
        $data['company_name'];

    if (
        !$departmentName
        ||
        !$companyName
    ) {

        return [
            'success' => false,
            'message' =>
                'Department name and company name are required.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    $company = Company::where(
        'name',
        'like',
        '%'.$companyName.'%'
    )->first();

    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole(
            'Company Admin'
        )
        &&
        auth()->user()->company_id
        !=
        $company->id
    ) {

        return [
            'success' => false,
            'message' =>
                'You can only manage departments of your own company.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findByName(
                $departmentName,
                $company->id
            );

    if (!$department) {

        return [
            'success' => false,
            'message' =>
                'Department not found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Additional Company Access Check
    |--------------------------------------------------------------------------
    */

    if (
        !$this->checkDepartmentCompanyAccess(
            $department
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You cannot access another company department.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Store Pending Delete Request
    |--------------------------------------------------------------------------
    */

    session([

        'pending_department_delete' => [

            'department_id' =>
                $department->id

        ]

    ]);

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>

            "⚠️ DELETE DEPARTMENT CONFIRMATION\n\n".
            "Department: {$department->name}\n".
            "Company: ".($department->company->name ?? 'N/A')."\n\n".
            "Type confirm to continue."

    ];

}

private function confirmDepartmentStatusUpdate(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !auth()->check()
        ||
        !auth()->user()->can(
            'departments.edit'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Pending Status Validation
    |--------------------------------------------------------------------------
    */

    $pending = session(
        'pending_department_status_update'
    );


    if (!$pending) {

        return [
            'success' => false,
            'message' =>
                'No pending department status update found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findById(
                $pending['department_id']
            );


    if (!$department) {

        session()->forget(
            'pending_department_status_update'
        );

        return [
            'success' => false,
            'message' =>
                'Department not found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole('Company Admin')
        &&
        $department->company_id
        !=
        auth()->user()->company_id
    ) {

        return [
            'success' => false,
            'message' =>
                'You cannot update another company department.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Store Old Values
    |--------------------------------------------------------------------------
    */

    $oldValues =
        $department->toArray();


    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    $department->status =
        $pending['status'];

    $department->save();


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    activity()
        ->causedBy(auth()->user())
        ->performedOn($department)
        ->withProperties([

            'user_id' =>
                auth()->id(),

            'module' =>
                'Departments',

            'action' =>
                'Status Update',

            'old_values' =>
                $oldValues,

            'new_values' =>
                $department->fresh()->toArray(),

            'ip_address' =>
                request()->ip(),

            'browser' =>
                request()->userAgent(),

            'source' =>
                'AI Assistant',

            'command' =>
                $command,

        ])
        ->log(
            'Department status updated via AI Assistant'
        );


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    auth()->user()->notify(

        new DepartmentActionNotification(

            "Department {$department->name} status updated successfully."

        )

    );


    /*
    |--------------------------------------------------------------------------
    | Clear Session
    |--------------------------------------------------------------------------
    */

    session()->forget(
        'pending_department_status_update'
    );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>

            "Department {$department->name} is now ".
            (
                $department->status
                ?
                'Active'
                :
                'Inactive'
            )

    ];

}
/*
|--------------------------------------------------------------------------
| Confirm Department Delete
|--------------------------------------------------------------------------
*/

private function confirmDepartmentDelete(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

    if (
        !auth()->check()
        ||
        !auth()->user()->can(
            'departments.delete'
        )
    ) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Pending Delete Validation
    |--------------------------------------------------------------------------
    */

    $pending = session(
        'pending_department_delete'
    );

    if (!$pending) {

        return [
            'success' => false,
            'message' =>
                'No pending department delete request found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findById(
                $pending['department_id']
            );

    if (!$department) {

        session()->forget(
            'pending_department_delete'
        );

        return [
            'success' => false,
            'message' =>
                'Department not found.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole(
            'Company Admin'
        )
        &&
        $department->company_id
        !=
        auth()->user()->company_id
    ) {

        return [
            'success' => false,
            'message' =>
                'You cannot delete another company department.'
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | Store Old Values
    |--------------------------------------------------------------------------
    */

    $oldValues =
        $department->toArray();

    $departmentName =
        $department->name;

    /*
    |--------------------------------------------------------------------------
    | Delete Department
    |--------------------------------------------------------------------------
    */

    $department->delete();

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    activity()
        ->causedBy(auth()->user())
        ->withProperties([

            'user_id' =>
                auth()->id(),

            'module' =>
                'Departments',

            'action' =>
                'Delete',

            'old_values' =>
                $oldValues,

            'new_values' =>
                null,

            'ip_address' =>
                request()->ip(),

            'browser' =>
                request()->userAgent(),

            'source' =>
                'AI Assistant',

            'command' =>
                $command,

        ])
        ->log(
            'Department deleted via AI Assistant'
        );

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    auth()->user()->notify(

        new DepartmentActionNotification(

            "Department {$departmentName} deleted successfully."

        )

    );

    /*
    |--------------------------------------------------------------------------
    | Clear Session
    |--------------------------------------------------------------------------
    */

    session()->forget(
        'pending_department_delete'
    );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>

            "Department {$departmentName} has been deleted successfully."

    ];

}

private function showDepartmentDetails(
    string $command
): array {

    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

if (
    !$this->hasDepartmentPermission(
        'departments.view'
    )
) {

        return [
            'success' => false,
            'message' =>
                'You do not have permission to view departments.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Parse Department + Company
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseDepartmentAndCompany(
            $command
        );


    $departmentName =
        $data['department_name'];


    $companyName =
        $data['company_name'];


    if (!$departmentName) {

        return [
            'success' => false,
            'message' =>
                'Department name is required.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Find Company
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole('Company Admin')
    ) {

        $company =
            Company::find(
                auth()->user()->company_id
            );

    } else {

    if (
    !auth()->user()->hasRole('Company Admin')
    &&
    empty($companyName)
) {

    return [
        'success' => false,
        'message' =>
            'Company name is required.'
    ];

}

        $company =
            Company::where(
                'name',
                'like',
                '%'.$companyName.'%'
            )->first();

    }


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

    if (
        auth()->user()->hasRole('Company Admin')
        &&
        auth()->user()->company_id != $company->id
    ) {

        return [
            'success' => false,
            'message' =>
                'You can only view your own company departments.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Find Department
    |--------------------------------------------------------------------------
    */

    $department =
        $this->departmentService
            ->findByName(
                $departmentName,
                $company->id
            );


    if (!$department) {

        return [
            'success' => false,
            'message' =>
                "Department {$departmentName} not found."
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return [

        'success' => true,

        'message' =>

            "📄 DEPARTMENT DETAILS\n\n".
            "Department: {$department->name}\n".
            "Company: {$department->company->name}\n".
            "Code: ".($department->code ?? 'N/A')."\n".
            "Description: ".($department->description ?? 'N/A')."\n".
            "Status: ".
            (
                $department->status
                ?
                'Active'
                :
                'Inactive'
            ).
            "\nCreated Date: ".
            $department->created_at->format('d-m-Y')

    ];

}

private function listDepartments(
    string $command
): array {


    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

if (
    !$this->hasDepartmentPermission(
        'departments.view'
    )
)
{
    return [
        'success' => false,
        'message' =>
            'You do not have permission to view departments.'
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Company Detection
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseDepartmentAndCompany(
            $command
        );


    if (
        auth()->user()->hasRole('Company Admin')
    ) {

        $company =
            Company::find(
                auth()->user()->company_id
            );

    } else {
  

if (
    auth()->user()->hasRole('Company Admin')
) {

    $company =
        Company::find(
            auth()->user()->company_id
        );

} else {

    if (
        empty($data['company_name'])
    ) {

        return [
            'success' => false,
            'message' =>
                'Company name is required.'
        ];

    }

    $company =
        Company::where(
            'name',
            'like',
            '%'.$data['company_name'].'%'
        )->first();

}


    if (!$company) {

        return [
            'success' => false,
            'message' =>
                'Company not found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Get Departments
    |--------------------------------------------------------------------------
    */

    $departments =
        $company->departments()
            ->get();


    if ($departments->isEmpty()) {

        return [
            'success' => false,
            'message' =>
                'No departments found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Build Response
    |--------------------------------------------------------------------------
    */

    $message =
        "📋 DEPARTMENTS LIST\n\n".
        "Company: {$company->name}\n\n";


    foreach (
        $departments as $index => $department
    ) {

        $message .=

            ($index + 1).
            ". {$department->name}\n".
            "   Code: ".
            ($department->code ?? 'N/A').
            "\n".
            "   Status: ".
            (
                $department->status
                ?
                'Active'
                :
                'Inactive'
            ).
            "\n\n";

    }


    return [

        'success' => true,

        'message' =>
            $message

    ];

}

private function searchDepartments(
    string $command
): array {


    /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

if (
    !$this->hasDepartmentPermission(
        'departments.view'
    )
)
{
    return [
        'success' => false,
        'message' =>
            'You do not have permission to view departments.'
    ];
}


    /*
    |--------------------------------------------------------------------------
    | Extract Keyword
    |--------------------------------------------------------------------------
    */

    $keyword =
        trim(
            str_ireplace(
                'search department',
                '',
                $command
            )
        );


    $keyword =
        preg_replace(
            '/\s+in company.*$/i',
            '',
            $keyword
        );


    if (!$keyword) {

        return [
            'success' => false,
            'message' =>
                'Search keyword is required.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Company Filter
    |--------------------------------------------------------------------------
    */

    $data =
        $this->parseDepartmentAndCompany(
            $command
        );


    if (
        auth()->user()->hasRole('Company Admin')
    ) {

        $companyId =
            auth()->user()->company_id;

    } else {

        $companyId = null;


        if ($data['company_name']) {

            $company =
                Company::where(
                    'name',
                    'like',
                    '%'.$data['company_name'].'%'
                )->first();


            if (!$company) {

                return [
                    'success' => false,
                    'message' =>
                        'Company not found.'
                ];

            }

            $companyId =
                $company->id;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Search Query
    |--------------------------------------------------------------------------
    */

    $query =
        \App\Models\Department::where(
            'name',
            'like',
            '%'.$keyword.'%'
        );


    if ($companyId) {

        $query->where(
            'company_id',
            $companyId
        );

    }


    $departments =
        $query->get();


    if ($departments->isEmpty()) {

        return [
            'success' => false,
            'message' =>
                'No matching departments found.'
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    $message =
        "🔎 DEPARTMENT SEARCH RESULT\n\n";


    foreach (
        $departments as $index => $department
    ) {

        $message .=

            ($index + 1).
            ". {$department->name}\n".
            "Company: {$department->company->name}\n".
            "Code: ".
            ($department->code ?? 'N/A').
            "\nStatus: ".
            (
                $department->status
                ?
                'Active'
                :
                'Inactive'
            ).
            "\n\n";

    }


    return [

        'success' => true,

        'message' =>
            $message

    ];

}

private function hasDepartmentPermission(
    string $permission
): bool {

    if (!auth()->check()) {
        return false;
    }


    return auth()->user()->can(
        $permission
    );

}
private function parseCreateDepartment(
    string $command
): array {

    preg_match(

        '/department\s+named\s+(.*?)\s+in\s+company\s+(.*?)\s+with\s+code\s+([^\s]+)(?:\s+description\s+(.*))?/i',

        $command,

        $matches

    );

    return [

        'name' =>
            trim(
                $matches[1] ?? ''
            ),

        'company_name' =>
            trim(
                $matches[2] ?? ''
            ),

        'code' =>
            trim(
                $matches[3] ?? ''
            ),

        'description' =>
            trim(
                $matches[4] ?? ''
            ),

    ];

}

private function checkDepartmentCompanyAccess(
    $department
): bool {

    if (
        auth()->user()
        ->hasRole('Company Admin')
    ) {

        if (!$department) {
    return false;
}

return
    $department->company_id
    ==
    auth()->user()->company_id;

    }


    return true;

}
/*
|--------------------------------------------------------------------------
| Parse Department + Company
|--------------------------------------------------------------------------
*/

private function parseDepartmentAndCompany(
    string $command
): array {

    preg_match(
        '/department\s+(.*?)\s+(?:in|from)\s+company\s+(.*)/i',
        $command,
        $matches
    );

    return [

        'department_name' =>
            trim(
                $matches[1] ?? ''
            ),

        'company_name' =>
            trim(
                $matches[2] ?? ''
            )

    ];

}
private function parseUpdateDepartmentCommand(
    string $command
): array {

    preg_match(
        '/department\s+(.*?)\s+in\s+company\s+(.*?)(?:\s+name|\s+code|\s+description|$)/i',
        $command,
        $matches
    );

    return [

        'department_name' =>
            trim(
                $matches[1] ?? ''
            ),

        'company_name' =>
            trim(
                $matches[2] ?? ''
            )

    ];

}
}
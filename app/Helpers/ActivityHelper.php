<?php

namespace App\Helpers;

use App\Models\Company;

class ActivityHelper
{
    public static function log(
        $causer,
        $subject,
        string $module,
        string $action,
        array $old = [],
        array $new = []
    ) {

        $companyId = null;

        // Company Module
        if ($subject instanceof Company) {

            $companyId = $subject->id;

        }

        // Department, User etc.
        elseif (isset($subject->company_id)) {

            $companyId = $subject->company_id;

        }

        activity()
            ->causedBy($causer)

            ->performedOn($subject)

            ->withProperties([
                "module" => $module,

                "action" => $action,

                "company_id" => $companyId,

                "subject_id" => $subject?->id,

                "subject_type" => class_basename($subject),

                "old" => $old,

                "new" => $new,

                "ip" => request()->ip(),

                "browser" => request()->userAgent(),
            ])

            ->log(ucfirst($module) . " " . $action);
    }
}
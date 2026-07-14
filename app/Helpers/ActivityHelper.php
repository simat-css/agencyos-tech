<?php

namespace App\Helpers;

class ActivityHelper
{

    public static function log(
        $causer,
        $subject,
        string $module,
        string $action,
        array $old = [],
        array $new = []
    )
    {

        activity()

            ->causedBy($causer)

            ->performedOn($subject)

            ->withProperties([

                'module' => $module,

                'action' => $action,

                'old' => $old,

                'new' => $new,

                'ip' => request()->ip(),

                'browser' => request()->userAgent(),

            ])

            ->log(
                ucfirst($module).' '.$action
            );

    }

}
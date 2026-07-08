<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {

        $stats = [

            'companies' => Company::count(),

            'departments' => Department::count(),

            'users' => User::count(),

            'projects' => 0,

        ];


        // Company growth chart
        $companyChart = Company::selectRaw(
                'MONTH(created_at) month, COUNT(*) total'
            )
            ->groupBy('month')
            ->pluck('total','month');


        $months = [
            1=>'Jan',
            2=>'Feb',
            3=>'Mar',
            4=>'Apr',
            5=>'May',
            6=>'Jun',
            7=>'Jul',
            8=>'Aug',
            9=>'Sep',
            10=>'Oct',
            11=>'Nov',
            12=>'Dec',
        ];


        $chartData = [];

        foreach($months as $key=>$month){

            $chartData[$month] = $companyChart[$key] ?? 0;

        }



        // Recent Activities
        $activities = Activity::latest()
            ->limit(5)
            ->get();



        return view(
            'dashboard',
            compact(
                'stats',
                'chartData',
                'activities'
            )
        );

    }
}
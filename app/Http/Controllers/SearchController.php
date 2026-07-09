<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use Spatie\Permission\Models\Role;  


class SearchController extends Controller
{

    public function search(Request $request)
    {

        $keyword = $request->get('q');


        if(!$keyword){
            return response()->json([]);
        }


        $users = User::where('name','LIKE',"%$keyword%")
            ->limit(5)
            ->get(['id','name','email']);


        $companies = Company::where('name','LIKE',"%$keyword%")
            ->limit(5)
            ->get(['id','name']);


        $departments = Department::where('name','LIKE',"%$keyword%")
            ->limit(5)
            ->get(['id','name']);


        $roles = Role::where('name','LIKE',"%$keyword%")
            ->limit(5)
            ->get(['id','name']);



        return response()->json([

            'users'=>$users,

            'companies'=>$companies,

            'departments'=>$departments,

            'roles'=>$roles

        ]);

    }

}
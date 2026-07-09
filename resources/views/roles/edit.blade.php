@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')

<div class="container-fluid">


    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">


        <div>

            <h2 class="mb-1">

                <i class="fas fa-user-edit text-warning me-2"></i>

                Edit Role

            </h2>


            <small class="text-muted">

                Update role details and permissions

            </small>


        </div>



        <a href="{{ route('roles.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left me-1"></i>

            Back

        </a>


    </div>





    <form action="{{ route('roles.update',$role) }}"
          method="POST">


        @csrf

        @method('PUT')



        <div class="row">



            {{-- Role Information --}}
            <div class="col-lg-4">


                <div class="card shadow-sm">


                    <div class="card-header">

                        <h5 class="mb-0">

                            <i class="fas fa-info-circle me-2"></i>

                            Role Information

                        </h5>


                    </div>



                    <div class="card-body">


                        <div class="mb-3">


                            <label class="form-label">

                                Role Name

                                <span class="text-danger">
                                    *
                                </span>


                            </label>


                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name',$role->name) }}">



                            @error('name')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                            @enderror


                        </div>





                        <div class="mb-3">


                            <label class="form-label">

                                Guard Name

                            </label>


                            <input type="text"
                                   class="form-control"
                                   value="{{ $role->guard_name }}"
                                   disabled>


                        </div>



                    </div>


                </div>


            </div>







            {{-- Permissions --}}
            <div class="col-lg-8">


                <div class="card shadow-sm">



                    <div class="card-header d-flex justify-content-between">


                        <h5 class="mb-0">


                            <i class="fas fa-key me-2"></i>

                            Assign Permissions


                        </h5>




                        <div>


                            <input type="checkbox"
                                   id="selectAll"
                                   class="form-check-input">


                            <label for="selectAll"
                                   class="ms-1">

                                Select All

                            </label>


                        </div>



                    </div>







                    <div class="card-body">


                    @php

                    $assignedPermissions =
                    $role->permissions
                    ->pluck('name')
                    ->toArray();


                    $groupedPermissions =
                    $permissions->groupBy(function($permission){

                        return explode('.', $permission->name)[0];

                    });


                    @endphp






                    @foreach($groupedPermissions as $group=>$permissionGroup)



                        <div class="mb-4">



                            <h6 class="text-primary text-uppercase">

                                {{ $group }}

                            </h6>


                            <hr>




                            <div class="row">


                            @foreach($permissionGroup as $permission)


                                <div class="col-md-4 mb-2">


                                    <div class="form-check">


                                        <input class="form-check-input permission-checkbox"
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="permission{{ $permission->id }}"

                                        {{ in_array(
                                            $permission->name,
                                            $assignedPermissions
                                        )
                                        ? 'checked'
                                        : ''
                                        }}

                                        >




                                        <label class="form-check-label"
                                               for="permission{{ $permission->id }}">


                                            {{ $permission->name }}


                                        </label>



                                    </div>



                                </div>


                            @endforeach


                            </div>



                        </div>



                    @endforeach





                    </div>



                </div>


            </div>




        </div>







        <div class="mt-4">


            <button type="submit"
                    class="btn btn-warning">


                <i class="fas fa-save me-1"></i>

                Update Role


            </button>




            <a href="{{ route('roles.index') }}"
               class="btn btn-secondary">

                Cancel

            </a>


        </div>





    </form>




</div>



@endsection





@push('scripts')

<script>


let permissions =
document.querySelectorAll('.permission-checkbox');


let selectAll =
document.getElementById('selectAll');



selectAll.addEventListener('change',function(){


    permissions.forEach(function(item){


        item.checked =
        selectAll.checked;


    });


});




// Auto check select all

function checkAllStatus(){


    let checked =
    document.querySelectorAll(
        '.permission-checkbox:checked'
    ).length;


    selectAll.checked =
    checked === permissions.length;


}


permissions.forEach(function(item){


    item.addEventListener(
        'change',
        checkAllStatus
    );


});


checkAllStatus();



</script>

@endpush
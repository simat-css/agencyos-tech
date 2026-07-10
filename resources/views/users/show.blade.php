@extends('layouts.admin')

@section('title', 'User Profile')

@section('content')

<div class="container-fluid">


    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">


        <div>

            <h2 class="mb-1">

                <i class="fas fa-user text-primary me-2"></i>

                User Profile

            </h2>


            <small class="text-muted">
                View complete user information
            </small>


        </div>



        <a href="{{ route('users.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left me-1"></i>

            Back

        </a>


    </div>





    <div class="row g-4">



        {{-- Profile Card --}}
        <div class="col-xl-4 col-lg-5 col-md-12">


            <div class="card shadow-sm border-0 text-center">


                <div class="card-body py-4">


                    @if($user->profile_photo)


                    <img src="{{ asset('storage/'.$user->profile_photo) }}"
                         class="rounded-circle mb-3"
                         width="120"
                         height="120">



                    @else


                    <div class="rounded-circle bg-secondary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:120px;height:120px;font-size:45px;">


                        {{ strtoupper(substr($user->name,0,1)) }}


                    </div>


                    @endif





                    <h4 class="mb-1">

                        {{ $user->name }}

                    </h4>



                    <p class="text-muted mb-3">

                        {{ $user->email }}

                    </p>




                    @if($user->status)


                        <span class="badge bg-success px-3 py-2">

                            Active

                        </span>


                    @else


                        <span class="badge bg-danger px-3 py-2">

                            Inactive

                        </span>


                    @endif



                </div>


            </div>


        </div>







        {{-- Details --}}
        <div class="col-xl-8 col-lg-7 col-md-12">


            <div class="card shadow-sm border-0">


                <div class="card-header bg-white">


                    <h5 class="mb-0">

                        <i class="fas fa-info-circle text-primary me-2"></i>

                        User Information

                    </h5>


                </div>





                <div class="card-body">


                    <div class="row g-4">



                        <div class="col-md-6">

                            <label class="text-muted small">
                                Name
                            </label>

                            <h6>
                                {{ $user->name }}
                            </h6>

                        </div>




                        <div class="col-md-6">

                            <label class="text-muted small">
                                Email
                            </label>


                            <h6>
                                {{ $user->email }}
                            </h6>


                        </div>





                        <div class="col-md-6">


                            <label class="text-muted small">
                                Company
                            </label>


                            <h6>

                                {{ $user->company->name ?? '-' }}

                            </h6>


                        </div>







                        <div class="col-md-6">


                            <label class="text-muted small">
                                Department
                            </label>


                            <h6>

                                {{ $user->department->name ?? '-' }}

                            </h6>


                        </div>







                        <div class="col-md-6">


                            <label class="text-muted small">
                                Roles
                            </label>


                            <div>


                            @foreach($user->roles as $role)


                                <span class="badge bg-primary me-1">

                                    {{ $role->name }}

                                </span>


                            @endforeach


                            </div>


                        </div>







                        <div class="col-md-6">


                            <label class="text-muted small">
                                Joined Date
                            </label>


                            <h6>

                                {{ $user->created_at->format('d M Y') }}

                            </h6>


                        </div>







                    </div>



                </div>


            </div>







            {{-- Action Buttons --}}
            <div class="mt-3 text-end">


                <a href="{{ route('users.edit',$user->id) }}"
                   class="btn btn-warning">


                    <i class="fas fa-edit me-1"></i>

                    Edit User


                </a>


            </div>



        </div>



    </div>



</div>


@endsection
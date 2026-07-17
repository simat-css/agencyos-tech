@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-user-circle text-primary me-2"></i>
                My Profile
            </h2>

            <small class="text-muted">
                View and manage your account information
            </small>
        </div>

        <div>

            <a href="{{ route('profile.edit') }}"
               class="btn btn-primary">

                <i class="fas fa-edit me-1"></i>
                Edit Profile

            </a>

        </div>

    </div>

    <div class="row">

        {{-- Left Card --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center">

                    @if(auth()->user()->profile_photo)

                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                             class="rounded-circle shadow mb-3"
                             width="120"
                             height="120">

                    @else

                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width:120px;height:120px;font-size:40px;">

                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}

                        </div>

                    @endif

                    <h4 class="mb-1">
                        {{ auth()->user()->name }}
                    </h4>

                    <p class="text-muted mb-3">
                        {{ auth()->user()->email }}
                    </p>

                    @foreach(auth()->user()->roles as $role)

                        <span class="badge bg-primary me-1">
                            {{ $role->name }}
                        </span>

                    @endforeach

                    <hr>

                    <div class="text-start">

                        <p>
                            <strong>Company:</strong>
                            {{ auth()->user()->company->name ?? '-' }}
                        </p>

                        <p>
                            <strong>Department:</strong>
                            {{ auth()->user()->department->name ?? '-' }}
                        </p>

                        <p>
                            <strong>Status:</strong>

                            @if(auth()->user()->status)

                                <span class="badge bg-success">
                                    Active
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Inactive
                                </span>

                            @endif

                        </p>

                    </div>

                </div>

            </div>

        </div>

        {{-- Right Section --}}
        <div class="col-lg-8">

            {{-- Account Information --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white">

                    <h5 class="mb-0">

                        <i class="fas fa-id-card text-primary me-2"></i>

                        Account Information

                    </h5>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Full Name
                            </label>

                            <h6>
                                {{ auth()->user()->name }}
                            </h6>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Email Address
                            </label>

                            <h6>
                                {{ auth()->user()->email }}
                            </h6>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Company
                            </label>

                            <h6>
                                {{ auth()->user()->company->name ?? '-' }}
                            </h6>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                Department
                            </label>

                            <h6>
                                {{ auth()->user()->department->name ?? '-' }}
                            </h6>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Quick Stats --}}
            <div class="row">

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="fas fa-history fa-2x text-primary mb-2"></i>

                            <h5>
                                {{ auth()->user()->created_at->format('d M Y') }}
                            </h5>

                            <small class="text-muted">
                                Member Since
                            </small>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>

                            <h5>
                                {{ auth()->user()->roles->count() }}
                            </h5>

                            <small class="text-muted">
                                Assigned Roles
                            </small>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <i class="fas fa-check-circle fa-2x text-warning mb-2"></i>

                            <h5>
                                {{ auth()->user()->status ? 'Active' : 'Inactive' }}
                            </h5>

                            <small class="text-muted">
                                Account Status
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
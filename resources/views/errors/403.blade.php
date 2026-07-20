@extends('layouts.admin')

@section('title', 'Access Denied')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mt-5">
                <div class="card-body text-center py-5">

                    <div class="mb-4">
                        <i class="fas fa-lock text-danger" style="font-size:80px;"></i>
                    </div>

                    <h1 class="display-4 fw-bold text-danger">
                        403
                    </h1>

                    <h4 class="mb-3">
                        Access Denied
                    </h4>

                   <p class="text-muted mb-4">
                     Access to this page has been restricted based on your current role and permissions.
                     If you need access, please contact your system administrator.
                    </p>

                    <a href="{{ url()->previous() }}"
                       class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i>
                        Go Back
                    </a>

                    <a href="{{ route('dashboard') }}"
                       class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
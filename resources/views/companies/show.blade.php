@extends('layouts.admin')

@section('title', 'Company Details')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="mb-0">
            <i class="fas fa-building me-2"></i>
            Company Details
        </h3>

        <div class="d-flex gap-2">

            <a href="{{ route('companies.edit',$company) }}"
               class="btn btn-warning">

                <i class="fas fa-edit me-1"></i>
                Edit

            </a>

            <a href="{{ route('companies.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left me-1"></i>
                Back

            </a>

        </div>

    </div>

    {{-- Company Card --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="row">

                {{-- Logo --}}
                <div class="col-md-3 text-center">

                    @if($company->logo)

                        <img
                            src="{{ asset('storage/'.$company->logo) }}"
                            class="img-fluid rounded shadow"
                            style="max-height:180px;">

                    @else

                        <div class="border rounded p-5 bg-light">

                            <i class="fas fa-building fa-5x text-secondary"></i>

                        </div>

                    @endif

                </div>

                {{-- Details --}}
                <div class="col-md-9">

                    <table class="table table-bordered align-middle">

                        <tr>

                            <th width="220">Company Name</th>

                            <td>{{ $company->name }}</td>

                        </tr>

                        <tr>

                            <th>Email</th>

                            <td>{{ $company->email ?: '-' }}</td>

                        </tr>

                        <tr>

                            <th>Phone</th>

                            <td>{{ $company->phone ?: '-' }}</td>

                        </tr>

                        <tr>

                            <th>Website</th>

                            <td>

                                @if($company->website)

                                    <a href="{{ $company->website }}"
                                       target="_blank">

                                        {{ $company->website }}

                                    </a>

                                @else

                                    -

                                @endif

                            </td>

                        </tr>

                        <tr>

                            <th>Status</th>

                            <td>

                                @if($company->status)

                                    <span class="badge bg-success">

                                        Active

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Inactive

                                    </span>

                                @endif

                            </td>

                        </tr>

                        <tr>

                            <th>Address</th>

                            <td>{{ $company->address ?: '-' }}</td>

                        </tr>

                        <tr>

                            <th>Created At</th>

                            <td>

                                {{ $company->created_at->format('d M Y h:i A') }}

                            </td>

                        </tr>

                        <tr>

                            <th>Updated At</th>

                            <td>

                                {{ $company->updated_at->format('d M Y h:i A') }}

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    {{-- Departments --}}
    <div class="card shadow-sm">

        <div class="card-header bg-light">

    <div class="d-flex justify-content-between align-items-center w-100">

        <h5 class="mb-0">
            <i class="fas fa-sitemap text-primary me-2"></i>

            Departments

            <span class="badge bg-primary">
                {{ $company->departments->count() }}
            </span>
        </h5>


        @if($company->status)

            <a href="{{ route('departments.create',['company'=>$company->id]) }}"
               class="btn btn-success btn-sm">

                <i class="fas fa-plus me-1"></i>
                Add Department

            </a>

        @else

            <span class="text-danger">
                <i class="fas fa-ban me-1"></i>
                Company is inactive
            </span>

        @endif

    </div>

</div>

        <div class="card-body p-0">

            @if($company->departments->count())

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="70">#</th>

                            <th>Department</th>

                            <th>Code</th>

                            <th>Status</th>

                            <th width="160">Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                    @foreach($company->departments as $department)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>

                                <strong>{{ $department->name }}</strong>

                            </td>

                            <td>

                                {{ $department->code ?: '-' }}

                            </td>

                            <td>

                                @if($department->status)

                                    <span class="badge bg-success">

                                        Active

                                    </span>

                                @else

                                    <span class="badge bg-danger">

                                        Inactive

                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('departments.show',$department) }}"
                                   class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a href="{{ route('departments.edit',$department) }}"
                                   class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            @else

                <div class="text-center py-5">

                    <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>

                    <h5>No Departments Found</h5>

                    <p class="text-muted">

                        This company doesn't have any departments yet.

                    </p>

                    @if($company->status)

                        <a href="{{ route('departments.create',['company'=>$company->id]) }}"
                           class="btn btn-primary">

                            <i class="fas fa-plus me-1"></i>

                            Create First Department

                        </a>

                    @endif

                </div>

            @endif

        </div>

    </div>

</div>

@endsection
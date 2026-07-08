@extends('layouts.admin')

@section('title', 'Department Details')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <div class="card card-primary card-outline">

                <div class="card-header">

                    <div class="d-flex justify-content-between align-items-center">

                        <h3 class="card-title mb-0">

                            <i class="fas fa-sitemap me-2"></i>

                            Department Details

                        </h3>

                        <div>

                            <a href="{{ route('departments.edit', $department) }}"
                               class="btn btn-warning">

                                <i class="fas fa-edit me-1"></i>

                                Edit

                            </a>

                            <a href="{{ route('departments.index') }}"
                               class="btn btn-secondary">

                                <i class="fas fa-arrow-left me-1"></i>

                                Back

                            </a>

                        </div>

                    </div>

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <tr>
                            <th width="220">Company</th>
                            <td>{{ $department->company->name }}</td>
                        </tr>

                        <tr>
                            <th>Department Name</th>
                            <td>{{ $department->name }}</td>
                        </tr>

                        <tr>
                            <th>Department Code</th>
                            <td>{{ $department->code ?: '-' }}</td>
                        </tr>

                        <tr>
                            <th>Description</th>
                            <td>{{ $department->description ?: '-' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>

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

                        </tr>

                        <tr>
                            <th>Created By</th>

                            <td>

                                {{ $department->creator->name ?? '-' }}

                            </td>

                        </tr>

                        <tr>
                            <th>Updated By</th>

                            <td>

                                {{ $department->updater->name ?? '-' }}

                            </td>

                        </tr>

                        <tr>
                            <th>Created At</th>

                            <td>

                                {{ $department->created_at->format('d M Y h:i A') }}

                            </td>

                        </tr>

                        <tr>
                            <th>Updated At</th>

                            <td>

                                {{ $department->updated_at->format('d M Y h:i A') }}

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
@extends('layouts.admin')

@section('title','Role Details')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h4>
                <i class="fas fa-user-shield me-2"></i>
                Role Details
            </h4>

        </div>

        <div class="card-body">

            <table class="table">

                <tr>
                    <th>Role Name</th>
                    <td>{{ $role->name }}</td>
                </tr>

                <tr>
                    <th>Type</th>
                    <td>

                        @if($role->is_system)

                            <span class="badge bg-primary">
                                System
                            </span>

                        @else

                            <span class="badge bg-success">
                                Custom
                            </span>

                        @endif

                    </td>
                </tr>

                <tr>
                    <th>Company</th>
                    <td>

                        {{ $role->company->name ?? 'System Role' }}

                    </td>
                </tr>

                <tr>
                    <th>Permissions</th>

                    <td>

                        @foreach($role->permissions as $permission)

                            <span class="badge bg-info mb-1">

                                {{ $permission->name }}

                            </span>

                        @endforeach

                    </td>

                </tr>

                <tr>

                    <th>Created</th>

                    <td>

                        {{ $role->created_at->format('d M Y h:i A') }}

                    </td>

                </tr>

            </table>

        </div>

    </div>

</div>

@endsection
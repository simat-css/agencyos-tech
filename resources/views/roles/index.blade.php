@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-user-shield text-primary me-2"></i>
                Roles & Permissions
            </h2>

            <small class="text-muted">
                Manage system and company roles
            </small>
        </div>

        @can('roles.create')
        <a href="{{ route('roles.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>
            Add Role
        </a>
        @endcan

    </div>


    {{-- Statistics --}}
<div class="row mb-4">

    {{-- Total Roles --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <small class="text-muted">
                        Total Roles
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $systemRoles->total() + $customRoles->total() }}
                    </h3>
                </div>

                <div class="text-primary fs-1">
                    <i class="fas fa-user-shield"></i>
                </div>

            </div>

        </div>
    </div>


    {{-- System Roles --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <small class="text-muted">
                        System Roles
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $systemRoles->total() }}
                    </h3>
                </div>

                <div class="text-success fs-1">
                    <i class="fas fa-shield-alt"></i>
                </div>

            </div>

        </div>
    </div>


    {{-- Custom Roles --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <small class="text-muted">
                        Custom Roles
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $customRoles->total() }}
                    </h3>
                </div>

                <div class="text-warning fs-1">
                    <i class="fas fa-users-cog"></i>
                </div>

            </div>

        </div>
    </div>


    {{-- Permissions --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">

            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <small class="text-muted">
                        Permissions
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{ $permissionsCount }}
                    </h3>
                </div>

                <div class="text-danger fs-1">
                    <i class="fas fa-key"></i>
                </div>

            </div>

        </div>
    </div>


</div>

    {{-- Search --}}
    <div class="card mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('roles.index') }}">

                <div class="input-group">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search role..."
                           value="{{ request('search') }}">

                    <button class="btn btn-primary">

                        <i class="fas fa-search me-1"></i>
                        Search

                    </button>

                    @if(request('search'))

                    <a href="{{ route('roles.index') }}"
                       class="btn btn-secondary">

                        Clear

                    </a>

                    @endif

                </div>

            </form>

        </div>

    </div>




    {{-- System Roles --}}
    <div class="card mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                <i class="fas fa-shield-alt me-2"></i>

                System Roles

            </h5>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                <tr>

                    <th width="60">#</th>
                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th width="180">Actions</th>

                </tr>

                </thead>

                <tbody>

                @forelse($systemRoles as $role)

                <tr>

                    <td>
                 <td>
                   {{ $systemRoles->firstItem() + $loop->index }}
                   </td>
                    </td>

                    <td>

                        <span class="badge bg-primary">

                            {{ $role->name }}

                        </span>

                    </td>

                    <td>

                        <span class="badge bg-info">

                            {{ $role->permissions->count() }}

                        </span>

                    </td>

                    <td>

                        {{ $role->created_at->format('d M Y') }}

                    </td>
                    

                    <td>
                        <a href="{{ route('roles.show',$role) }}"
   class="btn btn-info btn-sm">

    <i class="fas fa-eye"></i>

</a>

                        @if(auth()->user()->hasRole('Super Admin'))

                            <a href="{{ route('roles.edit',$role) }}"
                               class="btn btn-warning btn-sm">

                                <i class="fas fa-edit"></i>

                            </a>

                           @if($role->name !== 'Super Admin')

<form action="{{ route('roles.destroy',$role) }}"
      method="POST"
      class="d-inline delete-form">

    @csrf
    @method('DELETE')

    <button type="button"
            class="btn btn-danger btn-sm delete-btn">

        <i class="fas fa-trash"></i>

    </button>

</form>

@endif

                        @else

                            <span class="badge bg-secondary">

                                View Only

                            </span>

                        @endif

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5"
                        class="text-center">

                        No system roles found.

                    </td>

                </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{-- System Roles Pagination --}}
        <div class="mt-3">
          {{ $systemRoles->appends(request()->query())->links() }}
        </div>

    </div>



@if($customRoles->count() > 0)
    {{-- Custom Roles --}}
    <div class="card">

        <div class="card-header bg-success text-white">

            <h5 class="mb-0">

                <i class="fas fa-building me-2"></i>

                 Custom Roles

            </h5>

        </div>

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                <tr>

                    <th width="60">#</th>
                    <th>Role</th>
                    <th>Company ID</th>
                    <th>Company Name</th>
                    <th>Permissions</th>
                    <th width="180">Actions</th>

                </tr>

                </thead>

                <tbody>

                @forelse($customRoles as $role)

                <tr>

                    <td>
    {{ $customRoles->firstItem() + $loop->index }}
</td>

                    <td>

                        <span class="badge bg-success">

                            {{ $role->name }}

                        </span>

                    </td>

                    <td>

                        {{ $role->company_id }}

                    </td>

                    <td>

                        {{ $role->company->name ?? 'N/A' }}

                    </td>

                    <td>

                        <span class="badge bg-info">

                            {{ $role->permissions->count() }}

                        </span>

                    </td>

                    <td>
                         <a href="{{ route('roles.show',$role) }}"
   class="btn btn-info btn-sm">

    <i class="fas fa-eye"></i>

</a>
                        <a href="{{ route('roles.edit',$role) }}"
                           class="btn btn-warning btn-sm">

                            <i class="fas fa-edit"></i>

                        </a>

@if($role->name !== 'Super Admin')

<form action="{{ route('roles.destroy',$role) }}"
      method="POST"
      class="d-inline delete-form">

    @csrf
    @method('DELETE')

    <button type="button"
            class="btn btn-danger btn-sm delete-btn">

        <i class="fas fa-trash"></i>

    </button>

</form>

@endif

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="6"
                        class="text-center">

                        No custom roles found.

                    </td>

                </tr>

                @endforelse

                </tbody>

            </table>

               </div>

        {{-- Custom Roles Pagination --}}
        <div class="mt-3">
            {{ $customRoles->appends(request()->query())->links() }}
        </div>

    </div>
    @endif

</div>

@endsection
@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.delete-btn').forEach(button => {

        button.addEventListener('click', function () {

            let form = this.closest('.delete-form');

            Swal.fire({

                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',

                showCancelButton: true,

                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',

                confirmButtonText: 'Yes, delete it!'

            }).then((result) => {

                if (result.isConfirmed) {

                    form.submit();

                }

            });

        });

    });

});

</script>
@endpush
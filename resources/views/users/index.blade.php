@extends('layouts.admin')

@section('title', 'Users')

@section('content')

<div class="container-fluid">


    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-users text-primary me-2"></i>
                Users
            </h2>

            <small class="text-muted">
                Manage company users, roles and access
            </small>
        </div>

        {{-- Ajax Routes --}}
        <div id="user-routes" data-bulk-action="{{ route('users.bulk-action') }}" data-toggle-url="{{ url('users') }}">
        </div>


 <div class="btn-group">

    <button type="button"
            class="btn btn-success"
            data-bs-toggle="modal"
            data-bs-target="#importUsersModal">

        <i class="fas fa-file-import me-1"></i>
        Import

    </button>

    <a href="{{ route('users.export') }}"
       class="btn btn-info">

        <i class="fas fa-file-export me-1"></i>
        Export

    </a>
    <a href="{{ route('users.create') }}"
       class="btn btn-primary">

        <i class="fas fa-user-plus me-1"></i>
        Add User

    </a>

</div>


    </div>


{{-- User Statistics --}}
<div class="row mb-4">

    {{-- Total Users --}}
    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Users
                        </small>

                        <h2 class="fw-bold mb-0">
                            {{ $totalUsers }}
                        </h2>

                    </div>

                    <div class="bg-primary text-white rounded p-3">

                        <i class="fas fa-users fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Active Users --}}
    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Active Users
                        </small>

                        <h2 class="fw-bold text-success mb-0">
                            {{ $activeUsers }}
                        </h2>

                    </div>

                    <div class="bg-success text-white rounded p-3">

                        <i class="fas fa-user-check fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Inactive Users --}}
    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Inactive Users
                        </small>

                        <h2 class="fw-bold text-danger mb-0">
                            {{ $inactiveUsers }}
                        </h2>

                    </div>

                    <div class="bg-danger text-white rounded p-3">

                        <i class="fas fa-user-times fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


    {{-- Users Card --}}
    <div class="card shadow-sm border-0">
    {{-- Header --}}
    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h5 class="mb-0">
                <i class="fas fa-list text-primary me-2"></i>
                User List
            </h5>

        </div>

    </div>

    {{-- Filters --}}
    <div class="card-body border-bottom">

        <form method="GET" action="{{ route('users.index') }}">

            <div class="row g-3">

                <div class="col-xl-4 col-lg-4 col-md-12">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search Name, Email, Role..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <select name="company" class="form-select">
                        <option value="">Company</option>

                        @foreach($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ request('company') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <select name="department" class="form-select">
                        <option value="">Department</option>

                        @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                {{ request('department') == $department->id ? 'selected' : '' }}>
                                 {{ $department->name }} ({{ $department->company->name }}-{{ $department->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">
                    <select name="status" class="form-select">
                        <option value="">Status</option>

                        <option value="1"
                            {{ request('status') === '1' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="0"
                            {{ request('status') === '0' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-6">

    <div class="d-flex gap-2">

        <button type="submit"
                class="btn btn-primary flex-fill">

            <i class="fas fa-search me-1"></i>
            Search

        </button>

        @if(
            request()->filled('search') ||
            request()->filled('company') ||
            request()->filled('department') ||
            request()->filled('status')
        )

            <a href="{{ route('users.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-times"></i>

            </a>

        @endif

    </div>

</div>

            </div>

        </form>

    </div>


    @if($users->count() > 0)

<div class="card mb-3 border-0 shadow-sm">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center">

<div>

<h6 class="mb-0 fw-semibold">
Bulk Actions
</h6>

<small class="text-muted">
Apply actions on selected users
</small>

</div>


<div class="btn-group">


<button id="bulk-user-activate"
class="btn btn-outline-success">

<i class="fas fa-check-circle me-1"></i>
Activate

</button>


<button id="bulk-user-deactivate"
class="btn btn-outline-warning">

<i class="fas fa-ban me-1"></i>
Deactivate

</button>


<button id="bulk-user-delete"
class="btn btn-outline-danger">

<i class="fas fa-trash me-1"></i>
Delete

</button>


</div>

</div>

</div>

</div>

@endif

    {{-- Table --}}
   {{-- Table --}}
<div class="card-body p-0">

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0 agency-user-table">

            <thead>

                <tr>

                    <th width="50">
    <input type="checkbox" id="select-all-users">
</th>

<th width="70">#</th>

                    <th>User</th>

                    <th>Company</th>

                    <th>Department</th>

                    <th>Role</th>

                    <th width="140">Status</th>

                    <th width="170" class="text-center">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($users as $user)

                    <tr>
                        <td>
    <input type="checkbox"
           class="user-checkbox"
           value="{{ $user->id }}">
</td>

                        <td>
    {{ $users->firstItem() + $loop->index }}
</td>

                        {{-- User --}}
                        <td>

                            <div class="d-flex align-items-center">

                                @if($user->profile_photo)

                                    <img src="{{ asset('storage/'.$user->profile_photo) }}"
                                         alt="{{ $user->name }}"
                                         class="agency-user-avatar me-3">

                                @else

                                    <div class="agency-user-initial me-3">

                                        {{ strtoupper(substr($user->name,0,1)) }}

                                    </div>

                                @endif

                                <div>

                                    <div class="agency-user-name">

                                        {{ $user->name }}

                                    </div>

                                    <small class="text-muted">

                                        {{ $user->email }}

                                    </small>

                                </div>

                            </div>

                        </td>

                        {{-- Company --}}
                        <td>

                            <span class="agency-company-badge">

                                {{ $user->company->name ?? '-' }}

                            </span>

                        </td>

                        {{-- Department --}}
                        <td>

                            {{ $user->department->name ?? '-' }}

                        </td>

                        {{-- Role --}}
                        <td>

                            @forelse($user->roles as $role)

                                <span class="badge bg-primary me-1">

                                    {{ $role->name }}

                                </span>

                            @empty

                                <span class="text-muted">
                                   Unassigned
                                </span>

                            @endforelse

                        </td>

                        {{-- Status --}}
                        <td>

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input status-toggle"
                                    type="checkbox"
                                    data-id="{{ $user->id }}"
                                    {{ $user->status ? 'checked' : '' }}
                                >

                                <label class="form-check-label ms-2">

                                    @if($user->status)

                                        <span class="badge bg-success">

                                            Active

                                        </span>

                                    @else

                                        <span class="badge bg-danger">

                                            Inactive

                                        </span>

                                    @endif

                                </label>

                            </div>

                        </td>

                        {{-- Actions --}}
                        <td class="text-center">

                            <div class="btn-group">

    <a href="{{ route('users.show',$user->id) }}"
       class="btn btn-sm btn-info">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ route('users.edit',$user->id) }}"
       class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>

    <button type="button"
            class="btn btn-sm btn-danger delete-user-btn"
            data-id="{{ $user->id }}"
            data-name="{{ $user->name }}"
            title="Delete">

        <i class="fas fa-trash"></i>

    </button>

</div>

<form id="delete-user-form-{{ $user->id }}"
      action="{{ route('users.destroy', $user->id) }}"
      method="POST"
      style="display:none;">

    @csrf
    @method('DELETE')

</form>
                                

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8"
                            class="text-center py-5">

                            <div class="text-muted">

                                <i class="fas fa-users fa-3x mb-3"></i>

                                <br>

                                No users found

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

        <div class="card-footer">

            {{ $users->links() }}

        </div>



    </div>



</div>

{{-- Import modal popup --}}
<div class="modal fade"
     id="importUsersModal"
     tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="{{ route('users.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="fas fa-file-import me-2"></i>
                        Import Users

                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <label class="form-label">
                        Excel File
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".xlsx,.xls,.csv"
                           required>

                    <small class="text-muted">

                        Supported:
                        XLSX, XLS, CSV

                    </small>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-upload me-1"></i>
                        Import Users

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
@push('scripts')
<script src="{{ asset('assets/js/pages/users.js') }}"></script>
@endpush
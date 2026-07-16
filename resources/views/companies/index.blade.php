@extends('layouts.admin')

@section('title', 'Companies')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-building text-primary me-2"></i>
                Companies
            </h2>

            <small class="text-muted">
                Manage all companies in AgencyOS
            </small>
        </div>

        @role('Super Admin')

<a href="{{ route('companies.create') }}"
   class="btn btn-primary">

    <i class="fas fa-plus-circle me-1"></i>
    Add Company

</a>

@endrole

    </div>

    {{-- Success Message --}}
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        {{ session()->pull('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

    {{-- Statistics Cards --}}
<div class="row mb-4">

    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Companies
                        </small>

                        <h2 class="fw-bold mb-0">
                            {{ $totalCompanies }}
                        </h2>

                    </div>

                    <div class="bg-primary text-white rounded p-3">

                        <i class="fas fa-building fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Active Companies
                        </small>

                        <h2 class="fw-bold text-success mb-0">
                            {{ $activeCompanies }}
                        </h2>

                    </div>

                    <div class="bg-success text-white rounded p-3">

                        <i class="fas fa-check-circle fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Inactive Companies
                        </small>

                        <h2 class="fw-bold text-danger mb-0">
                            {{ $inactiveCompanies }}
                        </h2>

                    </div>

                    <div class="bg-danger text-white rounded p-3">

                        <i class="fas fa-times-circle fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
@role('Super Admin')
    {{-- Search Card --}}
    <div class="card mb-4">
    <div class="card-body">

        <form method="GET"
              action="{{ route('companies.index') }}">

            <div class="input-group">

                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search company by name, email, phone or website..."
                    value="{{ request('search') }}">

                <button
                    class="btn btn-primary">

                    <i class="fas fa-search me-1"></i>
                    Search

                </button>

                @if(request('search'))

                    <a href="{{ route('companies.index') }}"
                       class="btn btn-secondary">

                        <i class="fas fa-times me-1"></i>
                        Clear

                    </a>

                @endif

            </div>

        </form>

    </div>
</div>
@endrole

@if(auth()->user()->hasRole('Super Admin') && $companies->count() > 0)

<div class="card mb-3 border-0 shadow-sm">

    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between">

            <div>

                <h6 class="mb-0 fw-semibold">
                    Bulk Actions
                </h6>

                <small class="text-muted">
                    Apply actions on selected companies
                </small>

            </div>

            <div class="btn-group" id="company-actions" data-bulk-route="{{ route('companies.bulk-action') }}">

                <button id="bulk-activate"
                        class="btn btn-outline-success">

                    <i class="fas fa-check-circle me-1"></i>
                    Activate

                </button>

                <button id="bulk-deactivate"
                        class="btn btn-outline-warning">

                    <i class="fas fa-ban me-1"></i>
                    Deactivate

                </button>

                <button id="bulk-delete"
                        class="btn btn-outline-danger">

                    <i class="fas fa-trash me-1"></i>
                    Delete

                </button>

            </div>

        </div>

    </div>

</div>

@endif

    {{-- Companies Table --}}
    <div class="card">

        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-light">

                    <tr>
                        <th width="50">
    <input type="checkbox" id="select-all-companies">
</th>
                        <th width="70">Logo</th>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>
                        <th>Website</th>

                        <th>Status</th>
                        <th>Created</th>
                        <th>Created By</th>

                        <th width="180">Actions</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($companies as $company)

                    <tr>
                        <td>
    <input type="checkbox"
       class="company-checkbox"
       value="{{ $company->id }}"
       data-departments="{{ $company->departments()->count() }}">
</td>
                        <td>

                            @if($company->logo)

                                <img
                                    src="{{ asset('storage/'.$company->logo) }}"
                                    width="45"
                                    height="45"
                                    class="rounded border">

                            @else

                                <div class="text-center">

                                    <i class="fas fa-building fa-2x text-secondary"></i>

                                </div>

                            @endif

                        </td>

                        <td>{{ $company->id }}</td>

                        <td>{{ $company->name }}</td>

                        <td>{{ $company->email ?? '-' }}</td>

                        <td>{{ $company->phone ?? '-' }}</td>
                        <td>

                            @if($company->website)

                             <a href="{{ $company->website }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-primary">

                                <i class="fas fa-globe"></i>

                              </a>

                             @else

                            -

                             @endif

                        </td>

                        <td>

    <form
    action="{{ route('companies.toggle-status',$company) }}"
    method="POST"
    class="company-status-form"
    data-company="{{ $company->name }}"
    data-status="{{ $company->status ? 'active' : 'inactive' }}"
    data-departments="{{ $company->departments()->where('status',1)->count() }}">

        @csrf
        @method('PATCH')

        @if($company->status)

            <button
                type="submit"
                class="btn btn-success btn-sm">

                <i class="fas fa-check-circle me-1"></i>

                Active

            </button>

        @else

            <button
                type="submit"
                class="btn btn-danger btn-sm">

                <i class="fas fa-ban me-1"></i>

                Inactive

            </button>

        @endif

    </form>

</td>     
                     <td>

                       {{ $company->created_at->format('d M Y') }}

                    </td>
                    <td>

                        {{ $company->creator?->name ?? '-' }}

                    </td>

                        <td>

                            <a href="{{ route('companies.show',$company) }}"
                               class="btn btn-sm btn-info">

                                <i class="fas fa-eye"></i>

                            </a>

                            <a href="{{ route('companies.edit',$company) }}"
                               class="btn btn-sm btn-warning">

                                <i class="fas fa-edit"></i>

                            </a>
                            @if($company->status)

                          <a href="{{ route('departments.create', ['company' => $company->id]) }}"
                             class="btn btn-sm btn-success"
                             title="Add Department">

                            <i class="fas fa-sitemap"></i>

                               </a>

                            @endif

                            <button
                                class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal{{ $company->id }}">

                                <i class="fas fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                    {{-- Delete Modal --}}
                    <div
                        class="modal fade"
                        id="deleteModal{{ $company->id }}"
                        tabindex="-1">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <h5 class="modal-title">

                                        <i class="fas fa-trash text-danger me-2"></i>

                                        Delete Company

                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body">

                                    @php
                                    $departmentCount = $company->departments()->count();
                                    @endphp

                                   @if($departmentCount > 0)

                                  <div class="alert alert-warning">

                                  <i class="fas fa-exclamation-triangle me-2"></i>

                                  <strong>Warning!</strong>

                                  <br><br>

                                  This company has
                                 <strong>{{ $departmentCount }}</strong>
                                  department(s).

                                 Deleting this company will also
                                <strong>delete all associated departments.</strong>

                                 <br><br>

                                Are you sure you want to continue?

                        </div>

                        @else

                                   <p class="mb-0">

                                    Are you sure you want to delete
                                   <strong>{{ $company->name }}</strong>?

                                     </p>

                         @endif

                        </div>
                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">

                                        Cancel

                                    </button>

                                    <form
                                        action="{{ route('companies.destroy',$company) }}"
                                        method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-danger">

                                            <i class="fas fa-trash me-1"></i>

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                @empty

                    <tr>

                        <td colspan="7"
                            class="text-center py-5">

                            <i class="fas fa-building fa-3x text-muted mb-3"></i>

                            <br>

                            No companies found.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">

            {{ $companies->links() }}

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script src="{{ asset('assets/js/pages/companies.js') }}"></script>

@endpush
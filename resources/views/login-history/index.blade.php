@extends('layouts.admin')

@section('title', 'Login History')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-history text-primary me-2"></i>
                Login History
            </h2>

            <small class="text-muted">
                Monitor user login sessions and device activity
            </small>
        </div>

    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-2x text-primary mb-2"></i>
                    <h3 class="mb-1">{{ $stats['total_sessions'] ?? 0 }}</h3>
                    <small class="text-muted">Total Sessions</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h3 class="mb-1">
                          {{ $stats['active_sessions'] ?? 0 }}
                    </h3>
                    <small class="text-muted">Active Sessions</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-warning mb-2"></i>
                    <h3 class="mb-1">
                           {{ $stats['today_sessions'] ?? 0 }}
                    </h3>
                    <small class="text-muted">Today's Logins</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h3 class="mb-1">
                               {{ $stats['unique_users'] ?? 0 }}
                    </h3>
                    <small class="text-muted">Unique Users</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Search Filters --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-filter text-primary me-2"></i>
                Search & Filters
            </h5>
        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row g-3">

                    <div class="col-lg-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search User, Email, Browser, IP..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-lg-3">
                        <select name="status" class="form-select">

                            <option value="">All Status</option>

                            <option value="1"
                                {{ request('status') == '1' ? 'selected' : '' }}>
                                Active Session
                            </option>

                            <option value="0"
                                {{ request('status') == '0' ? 'selected' : '' }}>
                                Logged Out
                            </option>

                        </select>
                    </div>

                    <div class="col-lg-3">
                        <input type="date"
                               name="date"
                               class="form-control"
                               value="{{ request('date') }}">
                    </div>

                    <div class="col-lg-2">
                        <button type="submit"
                                class="btn btn-primary w-100">

                            <i class="fas fa-search me-1"></i>
                            Search

                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Login History Table --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="fas fa-list text-primary me-2"></i>
                Login Sessions
            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>User</th>
                            <th>Company</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>Platform</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Status</th>

                            @role('Super Admin')
                            <th width="80">Action</th>
                            @endrole

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($sessions as $session)

                            <tr>

                                <td>

                                    <div class="fw-semibold">
                                        {{ $session->user?->name ?? '-' }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $session->user?->email ?? '' }}
                                    </small>

                                </td>

                                <td>
                                    {{ $session->user?->company?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $session->ip_address }}
                                </td>

                                <td>
                                    {{ $session->browser }}
                                </td>

                                <td>
                                    {{ $session->platform }}
                                </td>

                                <td>
                                   {{ $session->login_at?->format('d M Y h:i A') }}
                                </td>

                                <td>

                                    @if($session->logout_at)

                                       {{ $session->logout_at?->format('d M Y h:i A') }}

                                    @else

                                        <span class="text-muted">-</span>

                                    @endif

                                </td>

                                <td>

                                    @if($session->is_active)

                                        <span class="badge bg-success">
                                            Active Session
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            Logged Out
                                        </span>

                                    @endif

                                </td>

                                @role('Super Admin')
<td>

    <form action="{{ route('login-history.destroy', $session->id) }}"
          method="POST"
          class="delete-form">

        @csrf
        @method('DELETE')

        <button type="button"
                class="btn btn-danger btn-sm btn-delete">

            <i class="fas fa-trash"></i>

        </button>

    </form>

</td>
@endrole

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="text-center py-5">

                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>

                                    <h5>No Login History Found</h5>

                                    <p class="text-muted mb-0">
                                        No login session records available.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">
                {{ $sessions->withQueryString()->links() }}
            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {

    // Success Message
    @if(session('success'))

        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
        });

    @endif


    // Delete Confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {

        button.addEventListener('click', function () {

            let form = this.closest('.delete-form');

            Swal.fire({

                title: 'Delete Login History?',
                text: "This record will be permanently deleted.",
                icon: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',

                reverseButtons: true

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
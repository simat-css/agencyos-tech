@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">
                <i class="fas fa-bell text-primary me-2"></i>
                Notifications
            </h3>

            <p class="text-muted mb-0">
                View and manage system notifications.
            </p>

        </div>

        <div class="mt-3 mt-md-0">

            <form
                action="{{ route('notifications.read-all') }}"
                method="POST">

                @csrf

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="fas fa-check-double me-2"></i>
                    Mark All Read

                </button>

            </form>

        </div>

    </div>

    {{-- Statistics Cards --}}
<div class="row mb-4">

    {{-- Total Notifications --}}
    <div class="col-md-6 col-xl-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted mb-1">
                            Total Notifications
                        </h6>

                        <h3 class="fw-bold mb-0">
                            {{ $totalNotifications }}
                        </h3>

                    </div>

                    <i class="fas fa-bell fa-2x text-primary"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Read Notifications --}}
    <div class="col-md-6 col-xl-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted mb-1">
                            Read
                        </h6>

                        <h3 class="fw-bold text-success mb-0">
                            {{ $readNotifications }}
                        </h3>

                    </div>

                    <i class="fas fa-check-circle fa-2x text-success"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Unread Notifications --}}
    <div class="col-md-6 col-xl-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted mb-1">
                            Unread
                        </h6>

                        <h3 class="fw-bold text-warning mb-0">
                            {{ $unreadNotifications }}
                        </h3>

                    </div>

                    <i class="fas fa-envelope fa-2x text-warning"></i>

                </div>

            </div>

        </div>

    </div>

</div>
   {{-- Search --}}
<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <form
            action="{{ route('notifications.index') }}"
            method="GET">

            <div class="row g-2">

                <div class="col-md">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search notifications..."
                        value="{{ request('search') }}">

                </div>

                <div class="col-auto">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fas fa-search me-2"></i>
                        Search

                    </button>

                </div>

                @if(request()->filled('search'))

                    <div class="col-auto">

                        <a
                            href="{{ route('notifications.index') }}"
                            class="btn btn-outline-secondary">

                            <i class="fas fa-rotate-left me-2"></i>
                            Reset

                        </a>

                    </div>

                @endif

            </div>

        </form>

    </div>

</div>

{{-- Bulk Action --}}
@if($notifications->count() > 0)

<div class="card border-0 shadow-sm mb-3">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h6 class="mb-0 fw-semibold">
                    Bulk Actions
                </h6>

                <small class="text-muted">
                    Delete selected notifications
                </small>

            </div>


            <button id="bulk-delete-notifications"
                    class="btn btn-outline-danger">

                <i class="fas fa-trash me-1"></i>
                Delete Selected

            </button>


        </div>

    </div>

</div>

@endif

    {{-- Notifications Table --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                Notification List
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

<tr>

    <th width="50">

        <input type="checkbox" id="select-all-notifications">

    </th>

    <th>Message</th>
    <th>Module</th>
    <th>Date</th>
    <th>Status</th>
    <th width="140">
        Action
    </th>

</tr>
                    </thead>

                    <tbody>

                        @forelse($notifications as $notification)

                            <tr>
                                <td>

<input type="checkbox"
       class="notification-checkbox"
       value="{{ $notification->id }}">

</td>

                                <td>

                                    <div class="fw-semibold">

                                        {{ $notification->data['message'] }}

                                    </div>

                                </td>

                                <td>

                                    <span class="badge bg-info">

                                        {{ $notification->data['module'] ?? 'System' }}

                                    </span>

                                </td>

                                <td>

                                    {{ $notification->created_at->diffForHumans() }}

                                </td>

                                <td>

                                    @if($notification->read_at)

                                        <span class="badge bg-success">

                                            Read

                                        </span>

                                    @else

                                        <span class="badge bg-warning text-dark">

                                            Unread

                                        </span>

                                    @endif

                                </td>

<td>

    <div class="d-flex align-items-center gap-2">

        @if(!$notification->read_at)

            <form
                action="{{ route('notifications.read', $notification->id) }}"
                method="POST">

                @csrf

                <button
                    type="submit"
                    class="btn btn-sm btn-outline-success"
                    title="Mark as Read">

                    <i class="fas fa-check"></i>

                </button>

            </form>

        @endif

        <form
            action="{{ route('notifications.destroy', $notification->id) }}"
            method="POST">

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="btn btn-sm btn-outline-danger"
                title="Delete Notification"
                onclick="return confirm('Delete this notification?')">

                <i class="fas fa-trash"></i>

            </button>

        </form>

    </div>

</td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center py-5">

                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>

                                    <h5 class="text-muted">

                                        No Notifications Found

                                    </h5>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="card-footer bg-white">

{{ $notifications->appends(request()->query())->links() }}
        </div>

    </div>

</div>

@endsection
@push('scripts')

@vite('resources/js/pages/notifications.js')

@endpush
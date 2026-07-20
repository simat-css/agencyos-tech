@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="mb-1">
            <i class="fas fa-history text-primary me-2"></i>
            Activity Logs
        </h2>

        <small class="text-muted">
            Track all user activities across the system
        </small>

    </div>

</div>


{{-- Statistics Cards --}}
<div class="row mb-4">

    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">
                    Total Logs
                </small>

                <h3 class="fw-bold mb-0">
                    {{ $totalLogs }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">
                    Today's Logs
                </small>

                <h3 class="fw-bold mb-0">
                    {{ $todayLogs }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">
                    This Week
                </small>

                <h3 class="fw-bold mb-0">
                    {{ $weekLogs }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-3 mb-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">
                    This Month
                </small>

                <h3 class="fw-bold mb-0">
                    {{ $monthLogs }}
                </h3>

            </div>

        </div>

    </div>

</div>


{{-- Action Statistics --}}
<div class="row mb-4">

    <div class="col-md-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Created Actions
                </small>

                <h3 class="fw-bold text-success mb-0">
                    {{ $createdLogs }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Updated Actions
                </small>

                <h3 class="fw-bold text-primary mb-0">
                    {{ $updatedLogs }}
                </h3>

            </div>

        </div>

    </div>


    <div class="col-md-4 mb-3">

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <small class="text-muted">
                    Deleted Actions
                </small>

                <h3 class="fw-bold text-danger mb-0">
                    {{ $deletedLogs }}
                </h3>

            </div>

        </div>

    </div>

</div>


<div class="card mb-4">

<div class="card-body">

<form method="GET">


<div class="row g-3">


<div class="col-md-3">

<label class="form-label">
User
</label>

<select name="user_id" class="form-select">

<option value="">
All Users
</option>


@foreach($users as $user)

<option value="{{ $user->id }}"
@if(request('user_id') == $user->id)
selected
@endif
>
{{ $user->name }}
</option>

@endforeach


</select>

</div>



<div class="col-md-2">

<label>
Module
</label>

<select name="module"
class="form-select">


<option value="">
All Modules
</option>


@foreach($modules as $module)

<option value="{{ $module }}"
@if(request('module')==$module)
selected
@endif
>
{{ $module }}
</option>

@endforeach


</select>

</div>



<div class="col-md-2">

<label>
Action
</label>

<select name="action"
class="form-select">


<option value="">
All Actions
</option>


@foreach($actions as $action)

<option value="{{ $action }}"
@if(request('action')==$action)
selected
@endif
>
{{ $action }}
</option>

@endforeach


</select>

</div>



<div class="col-md-2">

<label>
From
</label>

<input 
type="date"
name="date_from"
value="{{ request('date_from') }}"
class="form-control">

</div>



<div class="col-md-2">

<label>
To
</label>

<input 
type="date"
name="date_to"
value="{{ request('date_to') }}"
class="form-control">

</div>


</div>


<div class="row mt-3">


<div class="col-md-4">

<input 
type="text"
name="search"
value="{{ request('search') }}"
class="form-control"
placeholder="Search activity...">

</div>


<div class="col-md-2">

<button class="btn btn-primary">
<i class="fas fa-filter"></i>
Filter
</button>


@if(request()->hasAny(['user_id','module','action','date_from','date_to','search']))
    
    <a href="{{ route('activity-logs.index') }}"
       class="btn btn-secondary">
        Reset
    </a>

@endif


</div>


</div>



</form>


</div>

</div>


{{-- Activity Logs Table --}}
<div class="card shadow-sm border-0">

    <div class="card-body p-0">


        <div class="table-responsive">


            <table class="table table-hover align-middle mb-0">


                <thead class="table-light">


                    <tr>


                        <th>#</th>
<th>User</th>
<th>Module</th>
<th>Action</th>
<th>Description</th>
<th>IP Address</th>
<th>Date & Time</th>
<th>Actions</th>


                    </tr>


                </thead>



                <tbody>


                @forelse($logs as $log)



                    <tr>

    <td>
        {{ $loop->iteration + ($logs->currentPage()-1)*$logs->perPage() }}
    </td>

    <td>
        @if($log->causer)
            <strong>{{ $log->causer->name }}</strong>
            <br>
            <small class="text-muted">
                {{ $log->causer->email }}
            </small>
        @else
            System
        @endif
    </td>

    <td>
        <span class="badge bg-primary">
            {{ $log->properties['module'] ?? '-' }}
        </span>
    </td>

    <td>

        @php
            $action = $log->properties['action'] ?? '';
        @endphp

        @if($action == 'created')
            <span class="badge bg-success">Created</span>

        @elseif($action == 'updated')
            <span class="badge bg-warning text-dark">
                Updated
            </span>

        @elseif($action == 'deleted')
            <span class="badge bg-danger">
                Deleted
            </span>

        @elseif($action == 'restored')
    <span class="badge bg-success">
        Restored
    </span>

@elseif($action == 'status_updated')
    <span class="badge bg-info">
        Status Updated
    </span>

@elseif(str_contains($action,'bulk'))
    <span class="badge bg-dark">
        {{ ucwords(str_replace('_',' ',$action)) }}
    </span>    

        @else
            <span class="badge bg-secondary">
                {{ ucfirst($action) }}
            </span>
        @endif

    </td>

    <td>
        {{ $log->description }}
    </td>

    <td>
        {{ $log->properties['ip'] ?? '-' }}
    </td>

    <td>
        {{ $log->created_at->format('d M Y') }}
        <br>
        <small class="text-muted">
            {{ $log->created_at->format('h:i A') }}
        </small>
    </td>

    <td>

        <button
            class="btn btn-sm btn-info view-log-btn"
            data-bs-toggle="modal"
            data-bs-target="#viewLogModal"

            data-user="{{ $log->causer?->name ?? 'System' }}"
            data-company-id="{{ $log->properties['company_id'] ?? '-' }}"
            data-module="{{ $log->properties['module'] ?? '-' }}"
            data-action="{{ $log->properties['action'] ?? '-' }}"
            data-description="{{ $log->description }}"
            data-ip="{{ $log->properties['ip'] ?? '-' }}"
            data-browser="{{ $log->properties['browser'] ?? '-' }}"
            data-old='@json($log->properties["old"] ?? [])'
            data-new='@json($log->properties["new"] ?? [])'
            data-date="{{ $log->created_at->format('d M Y h:i A') }}"
        >

            <i class="fas fa-eye"></i>

        </button>

        @if(auth()->user()->hasRole('Super Admin'))

<form action="{{ route('activity-logs.destroy', $log->id) }}"
      method="POST"
      class="d-inline archive-log-form">

    @csrf
    @method('DELETE')

    <button type="submit" class="btn btn-sm btn-danger">
        <i class="fas fa-archive"></i>
    </button>

</form>

@endif

    </td>

</tr>


                @empty



                    <tr>


                        <td colspan="8" class="text-center py-4 text-muted">


                            No activity logs found


                        </td>


                    </tr>



                @endforelse



                </tbody>



            </table>



        </div>



    </div>



    <div class="card-footer bg-white">


        {{ $logs->links() }}


    </div>



</div>


{{-- View Activity Log Modal --}}
<div class="modal fade" id="viewLogModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content border-0 shadow">

            {{-- Header --}}
            <div class="modal-header bg-light ">

                <h5 class="modal-title">
                    <i class="fas fa-history me-2"></i>
                    Activity Log Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            {{-- Body --}}
            <div class="modal-body">

                {{-- Activity Information --}}
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-light">

                        <strong>
                            <i class="fas fa-info-circle me-2"></i>
                            Activity Information
                        </strong>

                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-bordered align-middle mb-0">

                                <tbody>

                                    <tr>
                                        <th width="220">User</th>
                                        <td id="logUser"></td>
                                    </tr>
                                    <tr>
    <th>Company ID</th>
    <td>
        <span class="badge bg-secondary fs-6" id="logCompanyId"></span>
    </td>
</tr>

                                    <tr>
                                        <th>Module</th>
                                        <td>
                                            <span class="badge bg-primary fs-6" id="logModule"></span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Action</th>
                                        <td>
                                            <span class="badge bg-warning text-dark fs-6" id="logAction"></span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Description</th>
                                        <td id="logDescription"></td>
                                    </tr>

                                    <tr>
                                        <th>IP Address</th>
                                        <td id="logIp"></td>
                                    </tr>

                                    <tr>
                                        <th>Browser</th>
                                        <td id="logBrowser"></td>
                                    </tr>

                                    <tr>
                                        <th>Date & Time</th>
                                        <td id="logDate"></td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                {{-- Changes Section --}}
                <div id="changesSection" style="display:none;">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-light">

                            <strong>
                                <i class="fas fa-exchange-alt me-2"></i>
                                Changes
                            </strong>

                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover table-bordered align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>
                                            <th width="20%">Field</th>
                                            <th width="40%">Old Value</th>
                                            <th width="40%">New Value</th>
                                        </tr>

                                    </thead>

                                    <tbody id="changesTableBody">

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    <i class="fas fa-times me-1"></i>
                    Close

                </button>

            </div>

        </div>

    </div>

</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        timer: 2500,
        showConfirmButton: false
    });
    @endif

});
</script>
<script src="{{ asset('assets/js/pages/activity-logs.js') }}"></script>
@endpush
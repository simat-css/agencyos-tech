@extends('layouts.admin')

@section('title', 'My Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        My Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, {{ auth()->user()->name }}.
    </p>

</div>

{{-- Statistics --}}
<div class="row g-4">

    {{-- Role --}}
    <div class="col-lg-3 col-md-6">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>My Role</small>

                    <h5 class="mb-0">
                        {{ auth()->user()->roles->first()?->name ?? 'N/A' }}
                    </h5>

                </div>

                <div class="stat-icon icon-primary">

                    <i class="fas fa-user-shield"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Company --}}
    <div class="col-lg-3 col-md-6">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Company</small>

                    <h5 class="mb-0">
                        {{ auth()->user()->company->name ?? 'N/A' }}
                    </h5>

                </div>

                <div class="stat-icon icon-info">

                    <i class="fas fa-building"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Department --}}
    <div class="col-lg-3 col-md-6">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Department</small>

                    <h5 class="mb-0">
                        {{ auth()->user()->department->name ?? 'N/A' }}
                    </h5>

                </div>

                <div class="stat-icon icon-success">

                    <i class="fas fa-sitemap"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Notifications --}}
    <div class="col-lg-3 col-md-6">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Unread Notifications</small>

                    <h3>
                        {{ auth()->user()->unreadNotifications()->count() }}
                    </h3>

                </div>

                <div class="stat-icon icon-warning">

                    <i class="fas fa-bell"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- Status --}}
    <div class="col-lg-3 col-md-6">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>

                    <small>Account Status</small>

                    <h5>
                        {!! auth()->user()->status
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-danger">Inactive</span>' !!}
                    </h5>

                </div>

                <div class="stat-icon icon-danger">

                    <i class="fas fa-user-check"></i>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- Quick Actions --}}
<div class="row mt-4">

    <div class="col-12">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>

            </div>

            <div class="card-body">

                <a href="{{ route('profile.show') }}"
                   class="btn btn-primary me-2 mb-2">

                    <i class="fas fa-user me-1"></i>
                    My Profile

                </a>

                <a href="{{ route('notifications.index') }}"
                   class="btn btn-warning me-2 mb-2">

                    <i class="fas fa-bell me-1"></i>
                    Notifications

                </a>

            </div>

        </div>

    </div>

</div>

{{-- Charts --}}
<div class="row mt-4">

    <div class="col-lg-8">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0">

                    <i class="fas fa-chart-line me-2"></i>

                    Activity Trend

                </h5>

            </div>

            <div class="card-body">

                <div id="activityChart"></div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0">

                    Notification Status

                </h5>

            </div>

            <div class="card-body">

                <div id="notificationChart"></div>

            </div>

        </div>

    </div>

</div>

{{-- Activities & Notifications --}}
<div class="row mt-4">

    {{-- Activities --}}
    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0">
                     <i class="fas fa-timeline text-primary me-2"></i>
                    Recent Activities
                </h5>

            </div>

            <div class="card-body">

                @forelse($activities ?? [] as $activity)

                    <div class="border-bottom pb-2 mb-3">

                        <i class="fas fa-history text-primary me-2"></i>

                        {{ is_object($activity) ? $activity->description : $activity }}

                        @if(is_object($activity) && isset($activity->created_at))

                            <div>

                                <small class="text-muted">

                                    {{ $activity->created_at->diffForHumans() }}

                                </small>

                            </div>

                        @endif

                    </div>

                @empty

                    <p class="text-muted mb-0">
                        No recent activities found.
                    </p>

                @endforelse

            </div>

        </div>

    </div>

    {{-- Notifications --}}
    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0">
                     <i class="fas fa-bell text-warning me-2"></i>
                    Recent Notifications
                </h5>

            </div>

            <div class="card-body">

                @forelse(auth()->user()->notifications->take(5) as $notification)

                    <div class="border-bottom pb-2 mb-3">

                        <div class="d-flex justify-content-between">

                            <span>
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </span>

                            @if(is_null($notification->read_at))

                                <span class="badge bg-danger">
                                    New
                                </span>

                            @endif

                        </div>

                        <small class="text-muted">

                            {{ $notification->created_at->diffForHumans() }}

                        </small>

                    </div>

                @empty

                    <p class="text-muted mb-0">

                        No notifications available.

                    </p>

                @endforelse

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    let activityChart = new ApexCharts(
        document.querySelector("#activityChart"),
        {
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },

            series: [{
                name: 'Activities',
                data: @json(array_values($activityData ?? []))
            }],

            xaxis: {
                categories: @json(array_keys($activityData ?? []))
            },

            colors: ['#2563EB'],

            stroke: {
                curve: 'smooth',
                width: 3
            },

            dataLabels: {
                enabled: false
            }
        }
    );

    activityChart.render();

    let notificationChart = new ApexCharts(
        document.querySelector("#notificationChart"),
        {
            chart: {
                type: 'donut',
                height: 350
            },

            series: @json(
                array_values(
                    ($notificationData ?? collect())->toArray()
                )
            ),

            labels: @json(
                array_keys(
                    ($notificationData ?? collect())->toArray()
                )
            ),

            colors: [
                '#22C55E',
                '#EF4444'
            ],

            legend: {
                position: 'bottom'
            },

            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            }
        }
    );

    notificationChart.render();

});

</script>

@endpush
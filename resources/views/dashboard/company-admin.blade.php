@extends('layouts.admin')

@section('title', 'Company Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        Company Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, Company Admin.
    </p>

</div>

<div class="row g-4">

    <div class="col-lg-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>Total Employees</small>
                    <h2>{{ $stats['employees'] }}</h2>
                </div>

                <div class="stat-icon icon-primary">
                    <i class="fas fa-users"></i>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>Departments</small>
                   <h2>{{ $stats['departments'] }}</h2>
                </div>

                <div class="stat-icon icon-success">
                    <i class="fas fa-sitemap"></i>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>Active Employees</small>
                   <h2>{{ $stats['activeEmployees'] }}</h2>
                </div>

                <div class="stat-icon icon-warning">
                    <i class="fas fa-briefcase"></i>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>Inactive Employees</small>
                    <h2>{{ $stats['inactiveEmployees'] }}</h2>
                </div>

                <div class="stat-icon icon-danger">
                    <i class="fas fa-user-slash"></i>
                </div>

            </div>
        </div>
    </div>

</div>
<div class="row mt-4">

    <div class="col-lg-8">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Employee Growth
                </h5>
            </div>

            <div class="card-body">

                <div id="employeeGrowthChart" style="height:350px;"></div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    Employees By Department
                </h5>
            </div>

            <div class="card-body">

                <div id="departmentChart" style="height:350px;"></div>

            </div>

        </div>

    </div>

</div>
<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">

    <h5 class="mb-0">

       <i class="fas fa-timeline text-primary me-2"></i>
Recent Activities

    </h5>

</div>

            <div class="card-body">

                @forelse($recentActivities as $activity)

<div class="activity-item mb-3">

    <i class="fas fa-history text-primary me-2"></i>

    {{ $activity->description }}

    <small class="text-muted d-block">
        {{ $activity->created_at->diffForHumans() }}
    </small>

</div>

@empty

<p class="text-muted mb-0">
    No recent activities found.
</p>

@endforelse

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
    <h5 class="mb-0">
        <i class="fas fa-bell text-warning me-2"></i>
        Notifications
    </h5>
</div>

            <div class="card-body">

                @forelse($recentNotifications as $notification)

<div class="mb-3">

    {{ $notification->data['message'] ?? 'Notification' }}

    <small class="text-muted d-block">
        {{ $notification->created_at->diffForHumans() }}
    </small>

</div>

@empty

<p class="text-muted mb-0">
    No notifications found.
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

    /*
    |--------------------------------------------------------------------------
    | Employee Growth Chart
    |--------------------------------------------------------------------------
    */

    let growthElement = document.querySelector(
        "#employeeGrowthChart"
    );

    if (growthElement) {

        let growthChart = new ApexCharts(
            growthElement,
            {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },

                series: [
                    {
                        name: 'Employees',

                        data: @json(
                            array_values($employeeGrowthData)
                        )
                    }
                ],

                xaxis: {
                    categories: @json(
                        array_keys($employeeGrowthData)
                    )
                },

                colors: [
                    '#2563EB'
                ],

                stroke: {
                    curve: 'smooth',
                    width: 3
                },

                dataLabels: {
                    enabled: false
                },

                fill: {
                    type: 'gradient',

                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1
                    }
                },

                tooltip: {
                    y: {
                        formatter: function (value) {

                            return value + " Employees";

                        }
                    }
                }
            }
        );

        growthChart.render();

    }

    /*
    |--------------------------------------------------------------------------
    | Employees By Department
    |--------------------------------------------------------------------------
    */

    let departmentElement = document.querySelector(
        "#departmentChart"
    );

    if (departmentElement) {

        let departmentChart = new ApexCharts(
            departmentElement,
            {
                chart: {
                    type: 'donut',
                    height: 350
                },

                series: @json(
                    $departmentData
                        ->pluck('users_count')
                        ->toArray()
                ),

                labels: @json(
                    $departmentData
                        ->pluck('name')
                        ->toArray()
                ),

                colors: [
                    '#2563EB',
                    '#22C55E',
                    '#F59E0B',
                    '#EF4444',
                    '#8B5CF6',
                    '#06B6D4',
                    '#EC4899',
                    '#84CC16'
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
                },

                dataLabels: {
                    enabled: true
                },

                tooltip: {
                    y: {
                        formatter: function (value) {

                            return value + " Employees";

                        }
                    }
                }
            }
        );

        departmentChart.render();

    }

});

</script>

@endpush
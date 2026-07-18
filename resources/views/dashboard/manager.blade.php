@extends('layouts.admin')

@section('title','Manager Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        Manager Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, Manager.
    </p>

</div>
<div class="row g-4">

    <div class="col-lg-3">
    <div class="card stat-card">
        <div class="card-body d-flex justify-content-between">
            <div>
                <small>Total Team Members</small>
                <h2>32</h2>
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
                <small>Active Members</small>
                <h2>28</h2>
            </div>

            <div class="stat-icon icon-success">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3">
    <div class="card stat-card">
        <div class="card-body d-flex justify-content-between">
            <div>
                <small>Departments Managed</small>
                <h2>3</h2>
            </div>

            <div class="stat-icon icon-warning">
                <i class="fas fa-sitemap"></i>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3">
    <div class="card stat-card">
        <div class="card-body d-flex justify-content-between">
            <div>
                <small>New Users This Month</small>
                <h2>6</h2>
            </div>

            <div class="stat-icon icon-danger">
                <i class="fas fa-user-plus"></i>
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
                    Team Performance
                </h5>
            </div>

            <div class="card-body">

                <div id="performanceChart" style="height:350px;"></div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    Task Status
                </h5>
            </div>

            <div class="card-body">

                <div id="taskChart" style="height:350px;"></div>

            </div>

        </div>

    </div>

</div>  
<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Recent Activities</h5>
            </div>

            <div class="card-body">

                <div class="activity-item mb-3">
    <i class="fas fa-user-plus text-success me-2"></i>
    New User Created
</div>

<div class="activity-item mb-3">
    <i class="fas fa-user-edit text-warning me-2"></i>
    User Updated
</div>

<div class="activity-item mb-3">
    <i class="fas fa-user-tag text-primary me-2"></i>
    Role Assigned
</div>

<div class="activity-item">
    <i class="fas fa-sitemap text-info me-2"></i>
    Department Updated
</div>
            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Notifications</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
    New employee joined
</div>

<div class="mb-3">
    User role updated
</div>

<div class="mb-3">
    Department assigned
</div>

<div>
    Account activated successfully
</div>

            </div>

        </div>

    </div>

</div>
@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    let performanceChart = new ApexCharts(
        document.querySelector("#performanceChart"),
        {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },

            series: [{
                name: 'Tasks Completed',
                data: [12, 18, 25, 32, 45, 58, 67]
            }],

            xaxis: {
                categories: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']
            },

            colors: ['#2563EB'],

            stroke: {
                curve: 'smooth',
                width: 3
            }
        }
    );

    performanceChart.render();

    let taskChart = new ApexCharts(
        document.querySelector("#taskChart"),
        {
            chart: {
                type: 'donut',
                height: 350
            },

            series: [67, 24, 8],

            labels: [
                'Completed',
                'Pending',
                'Overdue'
            ],

            colors: [
                '#22C55E',
                '#F59E0B',
                '#EF4444'
            ]
        }
    );

    taskChart.render();

});

</script>

@endpush

@endsection
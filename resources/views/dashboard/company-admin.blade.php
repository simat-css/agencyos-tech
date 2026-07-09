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
                    <h2>156</h2>
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
                    <h2>8</h2>
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
                    <small>Projects</small>
                    <h2>24</h2>
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
                    <small>Clients</small>
                    <h2>18</h2>
                </div>

                <div class="stat-icon icon-danger">
                    <i class="fas fa-user-tie"></i>
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
                <h5>Recent Activities</h5>
            </div>

            <div class="card-body">

                <div class="activity-item mb-3">
                    <i class="fas fa-user-plus text-success me-2"></i>
                    New employee added
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-sitemap text-primary me-2"></i>
                    Department updated
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-briefcase text-warning me-2"></i>
                    New project assigned
                </div>

                <div class="activity-item">
                    <i class="fas fa-user-edit text-info me-2"></i>
                    Employee profile updated
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
                    New employee joined today
                </div>

                <div class="mb-3">
                    Project deadline approaching
                </div>

                <div class="mb-3">
                    Department meeting scheduled
                </div>

                <div>
                    Monthly report ready
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    let growthChart = new ApexCharts(
        document.querySelector("#employeeGrowthChart"),
        {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            series: [{
                name: 'Employees',
                data: [20, 35, 45, 55, 75, 90, 120]
            }],
            xaxis: {
                categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul']
            },
            colors: ['#2563EB'],
            stroke: {
                curve: 'smooth',
                width: 3
            }
        }
    );

    growthChart.render();

    let departmentChart = new ApexCharts(
        document.querySelector("#departmentChart"),
        {
            chart: {
                type: 'donut',
                height: 350
            },
            series: [40, 25, 15, 20],
            labels: [
                'Development',
                'HR',
                'Sales',
                'Marketing'
            ],
            colors: [
                '#2563EB',
                '#22C55E',
                '#F59E0B',
                '#EF4444'
            ]
        }
    );

    departmentChart.render();

});

</script>

@endpush
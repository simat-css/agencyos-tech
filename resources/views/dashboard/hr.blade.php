@extends('layouts.admin')

@section('title', 'HR Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        HR Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, HR Manager.
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
                    <small>Active Employees</small>
                    <h2>142</h2>
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
                    <small>Inactive Employees</small>
                    <h2>14</h2>
                </div>

                <div class="stat-icon icon-danger">
                    <i class="fas fa-user-slash"></i>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>New Joiners</small>
                    <h2>12</h2>
                </div>

                <div class="stat-icon icon-warning">
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
                    Employees By Role
                </h5>
            </div>

            <div class="card-body">

                <div id="roleChart" style="height:350px;"></div>

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
                    <i class="fas fa-user-edit text-warning me-2"></i>
                    Employee profile updated
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-user-tag text-primary me-2"></i>
                    Role assigned
                </div>

                <div class="activity-item">
                    <i class="fas fa-user-check text-success me-2"></i>
                    Employee activated
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
                    Employee role updated
                </div>

                <div class="mb-3">
                    User account activated
                </div>

                <div>
                    Profile update pending
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
                data: [15, 25, 40, 55, 70, 95, 120]
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

    let roleChart = new ApexCharts(
        document.querySelector("#roleChart"),
        {
            chart: {
                type: 'donut',
                height: 350
            },

            series: [45, 20, 30, 15, 10],

            labels: [
                'Developers',
                'Designers',
                'HR',
                'Support',
                'Sales'
            ],

            colors: [
                '#2563EB',
                '#22C55E',
                '#F59E0B',
                '#EF4444',
                '#8B5CF6'
            ]
        }
    );

    roleChart.render();

});

</script>

@endpush
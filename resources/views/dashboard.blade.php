@extends('layouts.admin')

@section('title','Dashboard')

@section('content')

<div class="agency-dashboard">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h1 class="agency-page-title mb-1">
                Dashboard
            </h1>

            <p class="agency-page-subtitle mb-0">
                Welcome back, Super Admin.
            </p>
        </div>

    </div>

    <!-- Stats Cards -->
    <div class="row g-4">

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted d-block">
                            Total Companies
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ $stats['companies'] }}
                        </h3>

                    </div>

                    <div class="agency-stat-icon agency-primary">

                        <i class="fas fa-building"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted d-block">
                            Departments
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ $stats['departments'] }}
                        </h3>

                    </div>

                    <div class="agency-stat-icon agency-success">

                        <i class="fas fa-sitemap"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted d-block">
                            Users
                        </small>

                        <h3 class="fw-bold mb-0">
                            {{ $stats['users'] }}
                        </h3>

                    </div>

                    <div class="agency-stat-icon agency-warning">

                        <i class="fas fa-users"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted d-block">
                            Projects
                        </small>

                        <h3 class="fw-bold mb-0">
                            24
                        </h3>

                    </div>

                    <div class="agency-stat-icon agency-danger">

                        <i class="fas fa-briefcase"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Charts -->
    <div class="row g-4 mt-1">

        <div class="col-12 col-xl-8">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0">

                    <h5 class="mb-0">

                        <i class="fas fa-chart-line me-2"></i>

                        Company Growth
                    </h5>

                </div>

                <div class="card-body">

                    <div id="overviewChart"></div>

                </div>

            </div>

        </div>

        <div class="col-12 col-xl-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0">

                    <h5 class="mb-0">
                        Platform Summary
                    </h5>

                </div>

                <div class="card-body">

                    <div id="roleChart"></div>

                </div>

            </div>

        </div>

    </div>

    <!-- Activity & Notification -->
    <div class="row g-4 mt-1">

        <div class="col-12 col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0">

                    <h5 class="mb-0">
                               <i class="fas fa-timeline text-primary me-2"></i>
                        Recent Activities
                    </h5>

                </div>

                <div class="card-body">

                    @forelse($recentActivities as $activity)

<div class="agency-activity-item mb-3">

    <i class="fas fa-history text-primary me-2"></i>

    {{ $activity->description }}

    <small class="text-muted d-block">
        {{ $activity->created_at->diffForHumans() }}
    </small>

</div>

@empty

<p class="text-muted">
    No recent activities found.
</p>

@endforelse

                </div>

            </div>

        </div>

        <div class="col-12 col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0">

                    <h5 class="mb-0">
                         <i class="fas fa-bell text-warning me-2"></i>
                        Notifications
                    </h5>

                </div>

                <div class="card-body">

                    @forelse($recentNotifications as $notification)

<div class="agency-activity-item mb-3">

    {{ $notification->data['message'] ?? 'Notification' }}

    <small class="text-muted d-block">
        {{ $notification->created_at->diffForHumans() }}
    </small>

</div>

@empty

<p class="text-muted">
    No notifications found.
</p>

@endforelse

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    // =========================
    // Overview Area Chart
    // =========================

    let overviewElement = document.querySelector("#overviewChart");


    if (overviewElement) {


        let overviewOptions = {

            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },


            series: [
                {
                    name: 'Companies',

                    data: @json(array_values($companyData ?? []))
                }
            ],


            xaxis: {

                categories: @json(array_keys($companyData ?? []))

            },


            colors: ['#2563eb'],


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


            grid: {

                borderColor: '#e5e7eb'

            },


            tooltip: {

                y: {

                    formatter: function(value){

                        return value + " Companies";

                    }

                }

            }

        };


        let overviewChart = new ApexCharts(
            overviewElement,
            overviewOptions
        );


        overviewChart.render();

    }



    // =========================
    // Users Role Donut Chart
    // =========================


let roleElement = document.querySelector("#roleChart");

let roleOptions = {

    chart: {
        type: 'donut',
        height: 350
    },

    series: @json(array_values($roleData->toArray())),

    labels: @json(array_keys($roleData->toArray())),

    colors: [
        '#3b82f6',
        '#f59e0b',
        '#22c55e',
        '#ef4444'
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
    }
};

if (roleElement) {

    let roleChart = new ApexCharts(
        roleElement,
        roleOptions
    );

    roleChart.render();
}



});

</script>

@endpush
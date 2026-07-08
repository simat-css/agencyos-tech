@extends('layouts.admin')

@section('title','Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, Super Admin.
    </p>

</div>

<div class="row g-4">

    <div class="col-lg-3">

        <div class="card stat-card">

            <div class="card-body d-flex justify-content-between">

                <div>
                    <small>Total Companies</small>
                    <h2>{{ $stats['companies'] }}</h2>
                </div>

                <div class="stat-icon icon-primary">
                    <i class="fas fa-building"></i>
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
                    <small>Users</small>
                    <h2>{{ $stats['users'] }}</h2>
                </div>

                <div class="stat-icon icon-warning">
                    <i class="fas fa-users"></i>
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

                <div class="stat-icon icon-danger">
                    <i class="fas fa-briefcase"></i>
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
                    Overview
                </h5>
            </div>

            <div class="card-body">

                <div
                    id="overviewChart"
                    style="height:350px;">
                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    Users By Role
                </h5>
            </div>

            <div class="card-body">

                <div
                    id="roleChart"
                    style="height:350px;">
                </div>

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
                    <i class="fas fa-building text-primary me-2"></i>
                    Company Added
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-user-edit text-warning me-2"></i>
                    User Updated
                </div>

                <div class="activity-item">
                    <i class="fas fa-trash text-danger me-2"></i>
                    Company Deleted
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
                    New company registered
                </div>

                <div class="mb-3">
                    New user created
                </div>

                <div class="mb-3">
                    Project deadline approaching
                </div>

                <div>
                    Invoice payment received
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


    if(roleElement){


        let roleOptions = {


            chart: {

                type: 'donut',

                height:350

            },


            series: @json(array_values($roleData->toArray() ?? [])),


            labels: @json(array_keys($roleData->toArray() ?? [])),


            legend: {

                position:'bottom'

            },


            plotOptions: {

                pie: {

                    donut: {

                        size:'65%'

                    }

                }

            },


            dataLabels: {

                enabled:false

            }


        };


        let roleChart = new ApexCharts(
            roleElement,
            roleOptions
        );


        roleChart.render();


    }



});

</script>

@endpush
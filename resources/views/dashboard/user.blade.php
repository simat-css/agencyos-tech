@extends('layouts.admin')

@section('title', 'Employee Dashboard')

@section('content')

<div class="mb-4">

    <h1 class="dashboard-title">
        Employee Dashboard
    </h1>

    <p class="dashboard-subtitle">
        Welcome back, Employee.
    </p>

</div>

<!-- Statistics Cards -->

<div class="row g-4">

    <div class="col-lg-3">

        <div class="card stat-card">

            <div class="card-body text-center">

                <div class="stat-icon icon-primary mx-auto mb-3">
                    <i class="fas fa-user"></i>
                </div>

                <small>My Role</small>
                <h5>Developer</h5>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card stat-card">

            <div class="card-body text-center">

                <div class="stat-icon icon-success mx-auto mb-3">
                    <i class="fas fa-sitemap"></i>
                </div>

                <small>Department</small>
                <h5>Development</h5>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card stat-card">

            <div class="card-body text-center">

                <div class="stat-icon icon-warning mx-auto mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>

                <small>Account Status</small>
                <h5>Active</h5>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card stat-card">

            <div class="card-body text-center">

                <div class="stat-icon icon-danger mx-auto mb-3">
                    <i class="fas fa-bell"></i>
                </div>

                <small>Notifications</small>
                <h5>4</h5>

            </div>

        </div>

    </div>

</div>

<!-- Profile Section -->

<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Profile Completion</h5>
            </div>

            <div class="card-body">

                <div class="progress mb-3" style="height:10px;">

                    <div
                        class="progress-bar bg-success"
                        role="progressbar"
                        style="width:80%;">
                    </div>

                </div>

                <strong>80% Completed</strong>

                <p class="text-muted mt-2 mb-0">
                    Complete your profile information.
                </p>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>My Information</h5>
            </div>

            <div class="card-body">

                <p>
                    <strong>Employee ID:</strong>
                    EMP001
                </p>

                <p>
                    <strong>Email:</strong>
                    employee@example.com
                </p>

                <p>
                    <strong>Department:</strong>
                    Development
                </p>

                <p class="mb-0">
                    <strong>Role:</strong>
                    Developer
                </p>

            </div>

        </div>

    </div>

</div>

<!-- Activity + Chart -->

<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Recent Activities</h5>
            </div>

            <div class="card-body">

                <div class="activity-item mb-3">
                    <i class="fas fa-user-edit text-primary me-2"></i>
                    Profile Updated
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-key text-warning me-2"></i>
                    Password Changed
                </div>

                <div class="activity-item mb-3">
                    <i class="fas fa-sign-in-alt text-success me-2"></i>
                    Login Successful
                </div>

                <div class="activity-item">
                    <i class="fas fa-envelope text-info me-2"></i>
                    Notification Received
                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Profile Overview</h5>
            </div>

            <div class="card-body">

                <div id="profileChart" style="height:350px;"></div>

            </div>

        </div>

    </div>

</div>

<!-- Notifications -->

<div class="row mt-4">

    <div class="col-lg-12">

        <div class="card content-card">

            <div class="card-header bg-white border-0">
                <h5>Notifications</h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    Welcome to AgencyOS
                </div>

                <div class="mb-3">
                    Profile update reminder
                </div>

                <div class="mb-3">
                    Security notification
                </div>

                <div>
                    New company announcement
                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    let profileElement = document.querySelector('#profileChart');

    if (profileElement) {

        let profileChart = new ApexCharts(
            profileElement,
            {
                chart: {
                    type: 'donut',
                    height: 350
                },

                series: [80, 20],

                labels: [
                    'Profile Completed',
                    'Remaining'
                ],

                colors: [
                    '#22C55E',
                    '#E5E7EB'
                ],

                legend: {
                    position: 'bottom'
                },

                dataLabels: {
                    enabled: true
                }
            }
        );

        profileChart.render();
    }

});

</script>

@endpush
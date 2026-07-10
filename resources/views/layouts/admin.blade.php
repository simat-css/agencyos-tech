<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>@yield('title', 'AgencyOS')</title>

    {{-- Vite Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    {{-- Favicon --}}
    <link rel="icon"
          type="image/svg+xml"
          href="{{ asset('favicon.svg') }}">

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    {{-- AdminLTE --}}
    <link rel="stylesheet"
          href="{{ asset('adminlte/dist/css/adminlte.css') }}">

    {{-- AgencyOS Theme --}}
    <link rel="stylesheet"
          href="{{ asset('assets/css/agencyos.css') }}">

    @stack('styles')

</head>

<body class="agency-body">

<div class="agency-app-wrapper">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Navbar --}}
    @include('partials.navbar')

    {{-- Main Content --}}
    <main class="agency-main">

        <div class="agency-content container-fluid">

            @include('partials.breadcrumbs')

            @include('partials.alerts')

            @yield('content')

        </div>

    </main>

    {{-- Footer --}}
    @include('partials.footer')

</div>

{{-- AdminLTE --}}
<script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>

{{-- ApexCharts --}}
<script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>

{{-- SweetAlert2 --}}
<script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>

@stack('scripts')

</body>
</html>
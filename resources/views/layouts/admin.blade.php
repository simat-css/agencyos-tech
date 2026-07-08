<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','AgencyOS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet"
          href="{{ asset('adminlte/dist/css/adminlte.css') }}">

    <link rel="stylesheet"
          href="{{ asset('assets/css/agencyos.css') }}">
</head>

<body>

<div class="app-wrapper">

    @include('partials.sidebar')

    @include('partials.navbar')

    <main class="app-main">

        <div class="content-wrapper">

            @include('partials.breadcrumbs')

            @include('partials.alerts')

            @yield('content')

        </div>

    </main>

    @include('partials.footer')

</div>

<script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')

</body>
</html>
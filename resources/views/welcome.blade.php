<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgencyOS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-indigo-600 shadow">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">

            <div class="text-white text-2xl font-bold">
                <i class="fas fa-layer-group mr-2"></i>
                AgencyOS
            </div>

            <div class="space-x-3">
                <a href="{{ route('login') }}"
                   class="bg-white text-indigo-600 px-4 py-2 rounded font-medium hover:bg-gray-100">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="bg-indigo-800 text-white px-4 py-2 rounded font-medium hover:bg-indigo-900">
                    Register
                </a>
            </div>

        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-20 text-center">

        <h1 class="text-5xl font-bold text-gray-800 mb-6">
            Welcome to AgencyOS
        </h1>

        <p class="text-xl text-gray-600 mb-10">
            Complete Agency Management Platform with
            Companies, Departments, Roles, Permissions,
            Users, Activity Logs and AI Action Engine.
        </p>

        <div class="space-x-4">
            <a href="{{ route('login') }}"
               class="bg-indigo-600 text-white px-6 py-3 rounded-lg text-lg hover:bg-indigo-700">
                Get Started
            </a>

            <a href="{{ route('register') }}"
               class="border border-indigo-600 text-indigo-600 px-6 py-3 rounded-lg text-lg hover:bg-indigo-50">
                Create Account
            </a>
        </div>

    </section>

    <!-- Features -->
    <section class="container mx-auto px-6 pb-20">

        <div class="grid md:grid-cols-3 gap-6">

            <div class="bg-white p-6 rounded-lg shadow">
                <i class="fas fa-building text-indigo-600 text-3xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Company Management</h3>
                <p class="text-gray-600">
                    Manage multiple companies from a single platform.
                </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <i class="fas fa-users-cog text-indigo-600 text-3xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Roles & Permissions</h3>
                <p class="text-gray-600">
                    Advanced RBAC system with secure access control.
                </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <i class="fas fa-robot text-indigo-600 text-3xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">AI Action Engine</h3>
                <p class="text-gray-600">
                    Execute business actions through AI-powered commands.
                </p>
            </div>

        </div>

    </section>

    <!-- Footer -->
    <footer class="bg-white border-t py-6 text-center text-gray-500">
        © {{ date('Y') }} AgencyOS. All Rights Reserved.
    </footer>

</body>
</html>
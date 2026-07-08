@php
    use App\Models\Company;

    $hasCompany = Company::exists();
@endphp
<aside class="app-sidebar">

    <div class="sidebar-brand text-white p-4">
        <i class="fas fa-layer-group me-2"></i>
        AgencyOS
    </div>

    <div class="sidebar-menu p-3">

        <div class="menu-heading">
            Dashboard
        </div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-house me-2"></i>
            Dashboard
        </a>

        <div class="menu-heading mt-4">
            User Management
        </div>

        <!-- Company Dropdown -->
<div class="sidebar-dropdown">

    <a href="#companyMenu"
   class="sidebar-link"
   data-bs-toggle="collapse"
   aria-expanded="false">

    <span>
        <i class="fas fa-building me-2"></i>
        Companies
    </span>

    <i class="fas fa-angle-down"></i>
</a>

    <div class="collapse" id="companyMenu">

        <a href="{{ route('companies.index') }}" class="sidebar-sub-link">
            <i class="fas fa-list me-2"></i>
            Company List
        </a>

        <a href="{{ route('companies.create') }}" class="sidebar-sub-link">
            <i class="fas fa-plus me-2"></i>
            Add Company
        </a>

    </div>

</div>

        <!-- Department Dropdown -->
<div class="sidebar-dropdown">

    <a href="#departmentMenu"
       class="sidebar-link"
       data-bs-toggle="collapse"
       aria-expanded="{{ request()->routeIs('departments.*') ? 'true' : 'false' }}">

        <span>
            <i class="fas fa-sitemap me-2"></i>
            Departments
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->routeIs('departments.*') ? 'show' : '' }}"
         id="departmentMenu">

        <a href="{{ route('departments.index') }}"
           class="sidebar-sub-link {{ request()->routeIs('departments.index') ? 'active' : '' }}">

            <i class="fas fa-list me-2"></i>
            Department List

        </a>

        @if($hasCompany)

<a href="{{ route('departments.create') }}"
   class="sidebar-sub-link {{ request()->routeIs('departments.create') ? 'active' : '' }}">
    <i class="fas fa-plus me-2"></i>
    Add Department
</a>

@else

<a href="#"
   class="sidebar-sub-link disabled-link"
   title="Create a company first">

    <i class="fas fa-plus me-2"></i>
    Add Department

</a>

@endif

    </div>

</div>

        <a href="#">
            <i class="fas fa-user-shield me-2"></i>
            Roles
        </a>

        <a href="#">
            <i class="fas fa-lock me-2"></i>
            Permissions
        </a>

        <a href="#">
            <i class="fas fa-users me-2"></i>
            Users
        </a>

        <div class="menu-heading mt-4">
            CRM
        </div>

        <a href="#">
            <i class="fas fa-filter me-2"></i>
            Leads
        </a>

        <a href="#">
            <i class="fas fa-user-tie me-2"></i>
            Clients
        </a>

    </div>

</aside>
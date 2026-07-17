<aside class="app-sidebar bg-dark text-white">

    {{-- Sidebar Header --}}
    <div class="sidebar-brand d-flex justify-content-between align-items-center text-white p-3">

    <div>
        <i class="fas fa-layer-group me-2"></i>
        AgencyOS
    </div>

    <button
        type="button"
        id="sidebarClose"
        class="btn btn-sm text-white border-0">

        <i class="fas fa-times fs-5"></i>

    </button>

</div>

    <div class="p-3">

        <ul class="nav flex-column agency-sidebar-nav">

            {{-- Dashboard --}}
            <li class="nav-item mb-2">

                <a href="{{ route('dashboard') }}"
                   class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <i class="fas fa-house me-2"></i>
                    Dashboard

                </a>

            </li>

            {{-- User Management --}}
            <li class="mt-4 mb-2 text-uppercase small text-secondary">
                User Management
            </li>

            {{-- Companies --}}
            <li class="nav-item">

                <a class="nav-link text-white d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse"
                   href="#companyMenu"
                   role="button">

                    <span>
                        <i class="fas fa-building me-2"></i>
                        Companies
                    </span>

                    <i class="fas fa-angle-down"></i>

                </a>

                <div class="collapse {{ request()->routeIs('companies.*') ? 'show' : '' }}"
                     id="companyMenu">

                    <ul class="nav flex-column ms-3">

                        <li class="nav-item">

                            <a href="{{ route('companies.index') }}"
                               class="nav-link text-light {{ request()->routeIs('companies.index') ? 'active' : '' }}">

                                <i class="fas fa-list me-2"></i>
                                Company List

                            </a>

                        </li>
                        {{-- @role('Super Admin') --}}
                        <li class="nav-item">

                            <a href="{{ route('companies.create') }}"
                               class="nav-link text-light {{ request()->routeIs('companies.create') ? 'active' : '' }}">

                                <i class="fas fa-plus me-2"></i>
                                Add Company

                            </a>

                        </li>
                        {{-- @endrole --}}

                    </ul>

                </div>

            </li>

            {{-- Departments --}}
            <li class="nav-item">

                <a class="nav-link text-white d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse"
                   href="#departmentMenu"
                   role="button">

                    <span>
                        <i class="fas fa-sitemap me-2"></i>
                        Departments
                    </span>

                    <i class="fas fa-angle-down"></i>

                </a>

                <div class="collapse {{ request()->routeIs('departments.*') ? 'show' : '' }}"
                     id="departmentMenu">

                    <ul class="nav flex-column ms-3">

                        <li class="nav-item">

                            <a href="{{ route('departments.index') }}"
                               class="nav-link text-light {{ request()->routeIs('departments.index') ? 'active' : '' }}">

                                <i class="fas fa-list me-2"></i>
                                Department List

                            </a>

                        </li>

                        @if($hasCompany)

                            <li class="nav-item">

                                <a href="{{ route('departments.create') }}"
                                   class="nav-link text-light {{ request()->routeIs('departments.create') ? 'active' : '' }}">

                                    <i class="fas fa-plus me-2"></i>
                                    Add Department

                                </a>

                            </li>

                        @else

                            <li class="nav-item">

                                <a href="#"
                                   class="nav-link text-secondary disabled">

                                    <i class="fas fa-plus me-2"></i>
                                    Add Department

                                </a>

                            </li>

                        @endif

                    </ul>

                </div>

            </li>

            {{-- Roles --}}
            <li class="nav-item">

                <a class="nav-link text-white d-flex justify-content-between align-items-center"
                   data-bs-toggle="collapse"
                   href="#roleMenu"
                   role="button">

                    <span>
                        <i class="fas fa-user-shield me-2"></i>
                        Roles
                    </span>

                    <i class="fas fa-angle-down"></i>

                </a>

                <div class="collapse {{ request()->routeIs('roles.*') ? 'show' : '' }}"
                     id="roleMenu">

                    <ul class="nav flex-column ms-3">

                        <li class="nav-item">

                            <a href="{{ route('roles.index') }}"
                               class="nav-link text-light {{ request()->routeIs('roles.index') ? 'active' : '' }}">

                                <i class="fas fa-list me-2"></i>
                                Role List

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('roles.create') }}"
                               class="nav-link text-light {{ request()->routeIs('roles.create') ? 'active' : '' }}">

                                <i class="fas fa-plus me-2"></i>
                                Add Role

                            </a>

                        </li>

                    </ul>

                </div>

            </li>

            {{-- Users --}}
<li class="nav-item">

    <a class="nav-link text-white d-flex justify-content-between align-items-center"
       data-bs-toggle="collapse"
       href="#userMenu"
       role="button">

        <span>
            <i class="fas fa-users me-2"></i>
            Users
        </span>

        <i class="fas fa-angle-down"></i>

    </a>

    <div class="collapse {{ request()->routeIs('users.*') ? 'show' : '' }}"
         id="userMenu">

        <ul class="nav flex-column ms-3">

            <li class="nav-item">

                <a href="{{ route('users.index') }}"
                   class="nav-link text-light {{ request()->routeIs('users.index') ? 'active' : '' }}">

                    <i class="fas fa-list me-2"></i>
                    User List

                </a>

            </li>

            <li class="nav-item">

                <a href="{{ route('users.create') }}"
                   class="nav-link text-light {{ request()->routeIs('users.create') ? 'active' : '' }}">

                    <i class="fas fa-user-plus me-2"></i>
                    Add User

                </a>

            </li>

        </ul>

    </div>

</li>

        </ul>

    </div>

</aside>
<nav class="navbar navbar-expand navbar-dark top-navbar">

    <div class="container-fluid">

        <!-- Sidebar Toggle -->
        <button class="btn text-white border-0">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <span class="navbar-brand ms-2">
            AgencyOS
        </span>

        <div class="ms-auto d-flex align-items-center gap-3">

            <!-- Search -->
            <div class="search-wrapper">
                <input
                    type="text"
                    class="form-control search-box"
                    placeholder="Search..."
                >
            </div>

            <!-- Notifications -->
            <a href="#" class="nav-icon position-relative">
                <i class="fas fa-bell"></i>

                <span class="badge-count">
                    3
                </span>
            </a>

            <!-- Messages -->
            <a href="#" class="nav-icon position-relative">
                <i class="fas fa-envelope"></i>

                <span class="badge-count">
                    2
                </span>
            </a>

            <!-- User Dropdown -->
            <div class="dropdown">

                <a
                    href="#"
                    class="d-flex align-items-center text-decoration-none text-white"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                        alt="{{ auth()->user()->name }}"
                        width="40"
                        height="40"
                        class="rounded-circle border border-light"
                    >

                    <div class="ms-2 text-start">

                        <div class="fw-semibold">
                            {{ auth()->user()->name }}
                        </div>

                        <small class="text-light">

                            @if(auth()->user()->roles->count())
                                {{ auth()->user()->getRoleNames()->first() }}
                            @else
                                User
                            @endif

                        </small>

                    </div>

                    <i class="fas fa-chevron-down ms-2"></i>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow">

                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i>
                            My Profile
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i>
                            Settings
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item text-danger"
                            >
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </button>
                        </form>
                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>
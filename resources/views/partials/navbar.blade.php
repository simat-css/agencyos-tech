<nav class="navbar navbar-expand bg-primary navbar-dark shadow-sm agency-navbar">

    <div class="container-fluid">

        {{-- Sidebar Toggle --}}
        <button id="sidebarToggle"
                class="btn btn-link text-white p-0 border-0">

            <i class="fas fa-bars fs-5"></i>

        </button>

        {{-- Brand --}}
        <a class="navbar-brand fw-bold ms-3 mb-0">

            <i class="fas fa-layer-group me-2"></i>
            <span class="d-none d-md-inline">AgencyOS</span>

        </a>

        {{-- Right Section --}}
        <div class="ms-auto d-flex align-items-center gap-3">

            {{-- Search --}}
            <div class="position-relative d-none d-md-block agency-search">

                <div class="input-group">

                    <span class="input-group-text border-0 bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>

                    <input
                        type="text"
                        id="globalSearch"
                        class="form-control border-0"
                        placeholder="Search..."
                    >

                </div>

                <div id="searchResult"
                     class="search-result shadow">

                </div>

            </div>

            {{-- Notification --}}
            <div class="dropdown">

    <a href="#"
       class="position-relative text-white text-decoration-none"
       data-bs-toggle="dropdown">

        <i class="fas fa-bell fs-5"></i>

@if(auth()->user()->unreadNotifications()->count())
            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle">

                {{ auth()->user()->unreadNotifications()->count() }}

            </span>

        @endif

    </a>

    <div class="dropdown-menu dropdown-menu-end shadow notification-dropdown">

        @forelse(
            auth()->user()
                ->notifications()
                ->latest()
                ->take(5)
                ->get()
            as $notification
        )

            <div class="dropdown-item">

                <div class="fw-semibold">

                    {{ $notification->data['message'] }}

                </div>

                <small class="text-muted">

                    {{ $notification->created_at->diffForHumans() }}

                </small>

            </div>

        @empty

            <div class="dropdown-item text-muted">

                No notifications found

            </div>

        @endforelse

        <div class="dropdown-divider"></div>

        <a href="{{ route('notifications.index') }}"
           class="dropdown-item text-center">

            View All Notifications

        </a>

    </div>

</div>

            {{-- User Dropdown --}}
            <div class="dropdown">

                <a href="#"
                   class="d-flex align-items-center text-white text-decoration-none"
                   data-bs-toggle="dropdown">

                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}"
                        class="rounded-circle border"
                        width="40"
                        height="40"
                    >

                    <div class="ms-2 d-none d-lg-block">

                        <div class="fw-semibold">
                            {{ auth()->user()->name }}
                        </div>

                        <small>
                            {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                        </small>

                    </div>

                    <i class="fas fa-chevron-down ms-2"></i>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow">

                    <li>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user me-2"></i>
                            Profile
                        </a>
                    </li>

                    <li>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-history me-2"></i>
                            Login History
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>

                        <form action="{{ route('logout') }}" method="POST">

                            @csrf

                            <button type="submit"
                                    class="dropdown-item text-danger">

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
@push('scripts')

<script>

//search 
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("globalSearch");
    const resultBox = document.getElementById("searchResult");

    if (!searchInput || !resultBox) {
        return;
    }

    searchInput.addEventListener("keyup", function () {

        let keyword = this.value.trim();

        if (keyword.length < 2) {

            resultBox.innerHTML = "";
            resultBox.style.display = "none";

            return;
        }

        fetch(`{{ route('global.search') }}?q=${encodeURIComponent(keyword)}`)

            .then(response => response.json())

            .then(data => {

                let html = '';

                if (data.length > 0) {

                    data.forEach(item => {

                        html += `
                            <div class="result-item"
                                 data-url="${item.url}">

                                <div class="d-flex align-items-start">

                                    <i class="${item.icon} me-2 mt-1"></i>

                                    <div>

                                        <div>
                                            ${item.name}
                                        </div>

                                        <small class="text-muted">
                                            ${item.subtitle}
                                        </small>

                                    </div>

                                </div>

                            </div>
                        `;
                    });

                } else {

                    html = `
                        <div class="result-item text-muted">
                            No results found
                        </div>
                    `;
                }

                resultBox.innerHTML = html;
                resultBox.style.display = 'block';

            })

            .catch(error => {

                console.error(error);

            });

    });

    // Click suggestion

    resultBox.addEventListener('click', function (e) {

        let item = e.target.closest('.result-item');

        if (!item) {
            return;
        }

        let url = item.dataset.url;

        if (url) {
            window.location.href = url;
        }

    });

    // Hide dropdown

    document.addEventListener('click', function (e) {

        if (!e.target.closest('.agency-search')) {

            resultBox.style.display = 'none';

        }

    });

});


//responsive sidebar 
document.addEventListener('DOMContentLoaded', function () {

    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.querySelector('.app-sidebar');

    if (!sidebar) return;

    if (toggleBtn) {

        toggleBtn.addEventListener('click', function (e) {

    e.preventDefault();

    if(window.innerWidth <= 768){

        sidebar.classList.toggle('show');

    }

});

    }

    if (closeBtn) {

        closeBtn.addEventListener('click', function () {

            sidebar.classList.remove('show');

        });

    }

});
</script>

@endpush
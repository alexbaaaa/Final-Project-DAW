<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Swimming Up Admin')</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/adminArea.css') }}">
</head>
<body class="admin-back-shell">
    @php
        $currentAdminUser = $currentAdminUser ?? null;
        $currentAdminRole = $currentAdminUser?->role ?? session('admin_user_role');
        $adminBrand = $currentAdminUser?->alias ?? session('admin_user_alias', 'Swimming Up Admin');
        $canManageSwimmers = in_array($currentAdminRole, ['root', 'master'], true);
        $canManageEvents = in_array($currentAdminRole, ['root', 'master', 'admin'], true);
        $canManageCalendar = in_array($currentAdminRole, ['root', 'master', 'admin'], true);
        $canManageTrainingGroups = in_array($currentAdminRole, ['root', 'master', 'admin'], true);
        $canManageTimes = in_array($currentAdminRole, ['root', 'master', 'admin'], true);
        $canManageAppUsers = in_array($currentAdminRole, ['root', 'master'], true);
        $canViewAdminUsers = in_array($currentAdminRole, ['root', 'master'], true);
        $canCreateEvents = $canManageEvents;
    @endphp

    <header class="admin-topbar sticky-top shadow-sm">
        <nav class="navbar navbar-expand-lg py-3">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="{{ route('admin.home') }}">Welcome to the admin area <span>{{ $adminBrand }}</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-controls="adminMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="adminMenu">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-2">
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('admin.home')) active @endif" href="{{ route('admin.home') }}">Home</a>
                        </li>
                        @if($canManageSwimmers)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.swimmers.*')) active @endif" href="{{ route('admin.swimmers.index') }}">Swimmers</a>
                            </li>
                        @endif
                        @if($canManageEvents)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.events.*')) active @endif" href="{{ route('admin.events.index') }}">Events</a>
                            </li>
                        @endif
                        @if($canManageCalendar)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.calendar.*')) active @endif" href="{{ route('admin.calendar.index') }}">Calendar</a>
                            </li>
                        @endif
                        @if($canManageTrainingGroups)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.training_groups.*')) active @endif" href="{{ route('admin.training_groups.index') }}">Training Groups</a>
                            </li>
                        @endif
                        @if($canManageTimes)
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.times.*')) active @endif" href="{{ route('admin.times.index') }}">Times</a>
                            </li>
                        @endif
                        @if($canManageAppUsers || $canViewAdminUsers)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle @if(request()->routeIs('admin.users.*') || request()->routeIs('admin.users_admin.*')) active @endif" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Users
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($canManageAppUsers)
                                        <li>
                                            <a class="dropdown-item @if(request()->routeIs('admin.users.*')) active @endif" href="{{ route('admin.users.index') }}">APP</a>
                                        </li>
                                    @endif
                                    @if($canViewAdminUsers)
                                        <li>
                                            <a class="dropdown-item @if(request()->routeIs('admin.users_admin.*')) active @endif" href="{{ route('admin.users_admin.index') }}">Admin Area</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if($canCreateEvents)
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-sm admin-btn-primary mt-1 mt-lg-0" href="{{ route('admin.events.create') }}">Create Event</a>
                            </li>
                        @endif
                        <li class="nav-item ms-lg-2">
                            <form action="{{ route('admin.logout') }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary mt-1 mt-lg-0">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-4">
        @if(session('status_success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status_success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('status_error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('status_error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    @include('components.footer-back')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>

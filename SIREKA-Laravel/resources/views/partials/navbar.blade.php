@php
$user = currentUser();
@endphp
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3 shadow-xs">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary fs-4" href="{{ route('home') }}">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-1 px-2">
                <i class="bi bi-briefcase-fill fs-5"></i>
            </div>
            <span>SIREKA</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3">
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ ($activePage ?? '') === 'home' ? 'text-primary fw-bold' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ ($activePage ?? '') === 'about' ? 'text-primary fw-bold' : '' }}" href="{{ route('about') }}">Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ ($activePage ?? '') === 'jobs' ? 'text-primary fw-bold' : '' }}" href="{{ route('jobs') }}">Lowongan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ ($activePage ?? '') === 'helpdesk' ? 'text-primary fw-bold' : '' }}" href="{{ route('helpdesk') }}">Helpdesk</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                @if ($user)
                    @if (hasRole(['hr', 'admin']))
                        <a href="{{ homeUrl() }}" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>Admin Panel</span>
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle"></i>
                            <span>Dashboard ({{ explode(' ', $user['nama'])[0] }})</span>
                        </a>
                    @endif
                    <a href="{{ route('auth.logout') }}" class="btn btn-outline-danger" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('auth.login') }}" class="btn btn-outline-primary px-3">Login</a>
                    <a href="{{ route('auth.register') }}" class="btn btn-primary px-3">Register</a>
                @endif
            </div>
        </div>
    </div>
</nav>

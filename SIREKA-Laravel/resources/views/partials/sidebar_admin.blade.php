@php
$activeSidebar = $activeSidebar ?? 'dashboard';
$user = currentUser();
@endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-1 px-2">
            <i class="bi bi-shield-lock-fill fs-5"></i>
        </div>
        <span class="fs-5 leading-tight">SIREKA</span>
    </div>

    <!-- Tombol untuk menutup sidebar -->
    <button type="button" class="sidebar-close" id="sidebarClose" title="Tutup menu">
        <i class="bi bi-x-lg"></i>
    </button>

    <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2">
        <!-- Foto profil jika sudah diunggah, jika belum tampilkan huruf awal nama -->
        @if (!empty($user['profile_photo']))
            <img src="{{ url('/') }}/uploads/profiles/{{ $user['profile_photo'] }}"
                 class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;" alt="Foto profil">
        @else
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                {{ strtoupper(substr($user['nama'] ?? 'A', 0, 1)) }}
            </div>
        @endif
        <div class="overflow-hidden">
            <div class="fw-bold text-truncate" style="font-size: 0.88rem;">{{ $user['nama'] ?? 'Admin' }}</div>
            <span class="badge bg-primary text-white" style="font-size: 0.68rem;">{{ ($user['role'] ?? '') === 'admin' ? 'ADMIN' : 'HR' }}</span>
        </div>
    </div>

    <ul class="sidebar-nav">
        @if (hasRole('admin'))
        <!-- Menu admin: sistem, akun, dan data perusahaan -->
        <li class="sidebar-item">
            <a href="{{ route('admin.settings') }}" class="sidebar-link {{ $activeSidebar === 'settings' ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.users') }}" class="sidebar-link {{ $activeSidebar === 'users' ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Kelola Akun</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.divisions') }}" class="sidebar-link {{ $activeSidebar === 'divisions' ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span>Divisi IT</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.complaints') }}" class="sidebar-link {{ $activeSidebar === 'complaints' ? 'active' : '' }}">
                <i class="bi bi-chat-left-dots"></i>
                <span>Keluhan & Feedback</span>
            </a>
        </li>
        @else
        <!-- Menu HR: proses rekrutmen -->
        <li class="sidebar-item">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $activeSidebar === 'dashboard' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.jobs') }}" class="sidebar-link {{ $activeSidebar === 'jobs' ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i>
                <span>Lowongan Kerja</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.candidates') }}" class="sidebar-link {{ $activeSidebar === 'candidates' ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Data Kandidat</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.applications') }}" class="sidebar-link {{ $activeSidebar === 'applications' ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Lamaran Masuk</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.interviews') }}" class="sidebar-link {{ $activeSidebar === 'interviews' ? 'active' : '' }}">
                <i class="bi bi-calendar2-check"></i>
                <span>Jadwal Wawancara</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.loa_manage') }}" class="sidebar-link {{ $activeSidebar === 'loa' ? 'active' : '' }}">
                <i class="bi bi-award text-success"></i>
                <span>Letter of Acceptance (LoA)</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="{{ route('admin.talent_pool') }}" class="sidebar-link {{ $activeSidebar === 'talent_pool' ? 'active' : '' }}">
                <i class="bi bi-stars"></i>
                <span>Talent Pool</span>
            </a>
        </li>
        @endif
    </ul>

    <div class="p-3 border-top mt-auto">
        <a href="{{ route('home') }}" class="btn btn-sm btn-light w-100 d-flex align-items-center justify-content-center gap-2 mb-2">
            <i class="bi bi-house"></i>
            <span>Ke Halaman Publik</span>
        </a>
        <a href="{{ route('auth.logout') }}" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

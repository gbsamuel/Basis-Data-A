@extends('layouts.app')

@section('content')
<div class="dashboard-layout">
    @include('partials.sidebar_admin')

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary" id="sidebarToggle" title="Tampilkan menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Direktori Data Pelamar / Kandidat</h5>
                    <small class="text-muted">Daftar pencari kerja terdaftar dengan ringkasan keahlian dan status rekrutmen</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Search bar -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="{{ route('admin.candidates') }}" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, NIK, email, pendidikan..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="{{ route('admin.candidates') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                    <div class="col text-md-end small text-muted">
                        Total: <strong>{{ count($candidates) }}</strong> Kandidat
                    </div>
                </form>
            </div>

            <!-- Candidates Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Kontak</th>
                                <th>Pendidikan</th>
                                <th>Keahlian</th>
                                <th>Lamaran</th>
                                <th>Status Terakhir</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($candidates as $c)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-light text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                                {{ strtoupper(substr($c['nama'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $c['nama'] }}</div>
                                                <small class="text-muted">NIK: {{ $c['nik'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <div><i class="bi bi-envelope me-1 text-primary"></i> {{ $c['email'] }}</div>
                                        <div><i class="bi bi-telephone me-1 text-success"></i> {{ $c['no_telepon'] }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $c['pendidikan_terakhir'] }}</span>
                                        <small class="text-muted d-block">{{ $c['institusi'] }} &bull; {{ $c['status_pendidikan'] === 'Lulus' ? 'Lulus' : 'Perkiraan lulus' }} {{ $c['tahun_lulus'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border">
                                            {{ $c['total_skills'] }} Skill
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.applications') }}?search={{ urlencode($c['nama']) }}" class="badge bg-primary text-white text-decoration-none">
                                            {{ $c['total_applications'] }} Lamaran
                                        </a>
                                    </td>
                                    <td>
                                        @if ($c['latest_status'])
                                            {!! getStatusBadge($c['latest_status']) !!}
                                        @else
                                            <span class="badge bg-light text-muted border">Belum Melamar</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.applications') }}?search={{ urlencode($c['nama']) }}" class="btn btn-sm btn-outline-primary" title="Lihat Riwayat Lamaran">
                                            <i class="bi bi-eye me-1"></i> Dossier
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

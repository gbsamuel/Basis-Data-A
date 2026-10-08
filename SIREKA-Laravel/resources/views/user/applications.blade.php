@extends('layouts.app')

@section('content')
<div class="dashboard-layout">
    @include('partials.sidebar_user')

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary" id="sidebarToggle" title="Tampilkan menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Riwayat Lamaran Pekerjaan</h5>
                    <small class="text-muted">Kelola dan pantau seluruh posisi yang telah Anda lamar</small>
                </div>
            </div>
            <a href="{{ route('user.jobs') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Cari Lowongan Baru
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Status Tabs Filter -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('user.applications') }}" class="btn btn-sm {{ $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua Lamaran
                </a>
                <a href="{{ route('user.applications') }}?status=Applied" class="btn btn-sm {{ $statusFilter === 'Applied' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Applied
                </a>
                <a href="{{ route('user.applications') }}?status=HR+Review" class="btn btn-sm {{ $statusFilter === 'HR Review' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    HR Review
                </a>
                <a href="{{ route('user.applications') }}?status=Interview" class="btn btn-sm {{ $statusFilter === 'Interview' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Interview
                </a>
                <a href="{{ route('user.applications') }}?status=Accepted" class="btn btn-sm {{ $statusFilter === 'Accepted' ? 'btn-success text-white' : 'btn-outline-success' }}">
                    Accepted (Diterima)
                </a>
                <a href="{{ route('user.applications') }}?status=Rejected" class="btn btn-sm {{ $statusFilter === 'Rejected' ? 'btn-danger text-white' : 'btn-outline-danger' }}">
                    Rejected
                </a>
            </div>

            <!-- Applications Cards / Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                @if (empty($applications))
                    <div class="text-center py-5">
                        <div class="text-muted display-4 mb-2"><i class="bi bi-inbox"></i></div>
                        <h5 class="fw-bold">Tidak Ada Lamaran Ditemukan</h5>
                        <p class="text-muted small">Anda belum memiliki lamaran dengan status yang dipilih.</p>
                        <a href="{{ route('user.jobs') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> Mulai Melamar Pekerjaan
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi & Perusahaan</th>
                                    <th>Tanggal Apply</th>
                                    <th>Match Score</th>
                                    <th>Status Saat Ini</th>
                                    <th>Berkas CV</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $app)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $app['nama_job'] }}</div>
                                            <div class="small text-muted">
                                                <i class="bi bi-building me-1"></i> {{ $app['nama_company'] }} &bull; {{ $app['nama_divisi'] }}
                                            </div>
                                            <small class="badge bg-light text-muted border mt-1">{{ $app['job_type'] }}</small>
                                        </td>
                                        <td class="small text-muted">
                                            {{ formatTanggalIndo($app['applied_at']) }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress-match flex-grow-1" style="width: 70px;">
                                                    <div class="progress-bar {{ $app['match_score'] >= 80 ? 'bg-success' : ($app['match_score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                         style="width: {{ $app['match_score'] }}%"></div>
                                                </div>
                                                <span class="small fw-bold">{{ $app['match_score'] }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            {!! getStatusBadge($app['current_status']) !!}
                                            @if ($app['has_interview'] > 0)
                                                <span class="badge bg-warning text-dark mt-1 d-inline-block">
                                                    <i class="bi bi-calendar-check me-1"></i> Jadwal Interview Ada
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ url('/') }}/uploads/cv/{{ $app['cv_file'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Lihat CV
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('user.tracking') }}?id={{ $app['id_application'] }}" class="btn btn-sm btn-outline-primary" title="Tracking Visual">
                                                    <i class="bi bi-clock-history me-1"></i> Tracking
                                                </a>
                                                @if ($app['current_status'] === 'Accepted')
                                                    <a href="{{ route('user.loa') }}?app_id={{ $app['id_application'] }}" class="btn btn-sm btn-success" title="Cetak Surat Penerimaan">
                                                        <i class="bi bi-award me-1"></i> LoA
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection

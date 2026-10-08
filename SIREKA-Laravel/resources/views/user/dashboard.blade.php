@extends('layouts.app')

@section('content')
<div class="dashboard-layout">
    @include('partials.sidebar_user')

    <main class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary" id="sidebarToggle" title="Tampilkan menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Dashboard Pelamar</h5>
                    <small class="text-muted">Selamat datang kembali, {{ $user['nama'] }}</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Welcome Banner -->
            <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white" style="border-left: 5px solid var(--primary-color) !important;">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-primary-subtle text-primary mb-2 fw-bold border border-primary-subtle">SIREKA Candidate Space</span>
                        <h3 class="fw-bold text-dark mb-1">Hai, {{ $user['nama'] }}!</h3>
                        <p class="text-secondary mb-3 small" style="max-width: 580px;">
                            Pantau kemajuan lamaran Anda secara transparan. Sistem secara otomatis menghitung kecocokan keahlian Anda dengan setiap lowongan yang dibuka oleh PT Solusi Teknologi Nusantara.
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('user.jobs') }}" class="btn btn-warning text-dark btn-sm fw-bold shadow-sm">
                                <i class="bi bi-search me-1"></i> Eksplorasi Lowongan Baru
                            </a>
                            <a href="{{ route('user.profile') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-stars me-1"></i> Kelola Keahlian ({{ $userSkillsCount }} Skill)
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end d-none d-lg-block">
                        <i class="bi bi-person-workspace display-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>

            <!-- Upcoming Interview Alert if scheduled -->
            @if (!empty($upcomingInterviews))
                <div class="card-custom p-4 mb-4 border-warning bg-warning-subtle shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-camera-video text-warning me-2"></i> Jadwal Wawancara Mendatang</h5>
                        <span class="badge bg-warning text-dark">Panggilan Interview</span>
                    </div>
                    @foreach ($upcomingInterviews as $itw)
                        <div class="p-3 bg-white rounded-3 mt-2 border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="fw-bold text-dark">{{ $itw['nama_job'] }} - {{ $itw['nama_company'] }}</div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar-check me-1 text-primary"></i> {{ formatTanggalIndo($itw['tanggal']) }} &bull; 
                                    <i class="bi bi-clock me-1 text-primary"></i> {{ substr($itw['waktu'], 0, 5) }} WIB &bull; 
                                    <i class="bi bi-person me-1 text-primary"></i> Interviewer: {{ $itw['nama_interviewer'] }}
                                </div>
                            </div>
                            <div>
                                @if (!empty($itw['meeting_link']))
                                    <a href="{{ $itw['meeting_link'] }}" target="_blank" class="btn btn-success btn-sm fw-bold">
                                        <i class="bi bi-link-45deg me-1"></i> Buka Tautan Meeting
                                    </a>
                                @endif
                                <a href="{{ route('user.interview') }}" class="btn btn-outline-secondary btn-sm">Detail</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Summary Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Total Lamaran</div>
                                <div class="fs-3 fw-bold text-primary">{{ (int)($counts['total_applied'] ?? 0) }}</div>
                            </div>
                            <div class="bg-primary-light text-primary rounded-3 p-2">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Sedang Diproses</div>
                                <div class="fs-3 fw-bold text-warning">{{ (int)($counts['in_process'] ?? 0) }}</div>
                            </div>
                            <div class="bg-warning-subtle text-warning rounded-3 p-2">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Interview</div>
                                <div class="fs-3 fw-bold text-info">{{ (int)($counts['interview_count'] ?? 0) }}</div>
                            </div>
                            <div class="bg-info-subtle text-info rounded-3 p-2">
                                <i class="bi bi-camera-video fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">Diterima</div>
                                <div class="fs-3 fw-bold text-success">{{ (int)($counts['accepted_count'] ?? 0) }}</div>
                            </div>
                            <div class="bg-success-subtle text-success rounded-3 p-2">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications Table -->
            <div class="card-custom p-4 border-0 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Lamaran Pekerjaan Terbaru</h5>
                        <small class="text-muted">Daftar riwayat lamaran yang telah Anda ajukan</small>
                    </div>
                    <a href="{{ route('user.applications') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua Lamaran <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                @if (empty($recentApps))
                    <div class="text-center py-5">
                        <div class="text-muted display-4 mb-2"><i class="bi bi-file-earmark-x"></i></div>
                        <h6 class="fw-bold">Belum Ada Lamaran yang Diajukan</h6>
                        <p class="text-muted small">Cari lowongan pekerjaan yang sesuai dengan keahlian dan minat Anda sekarang.</p>
                        <a href="{{ route('user.jobs') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> Jelajahi Lowongan
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi Pekerjaan</th>
                                    <th>Perusahaan & Divisi</th>
                                    <th>Tanggal Melamar</th>
                                    <th>Match Score</th>
                                    <th>Status Saat Ini</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentApps as $app)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $app['nama_job'] }}</div>
                                            <small class="badge bg-light text-muted border">{{ $app['job_type'] }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-secondary">{{ $app['nama_company'] }}</div>
                                            <small class="text-muted">{{ $app['nama_divisi'] }}</small>
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
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('user.tracking') }}?id={{ $app['id_application'] }}" class="btn btn-sm btn-outline-primary" title="Tracking Timeline">
                                                <i class="bi bi-clock-history me-1"></i> Tracking
                                            </a>
                                            @if ($app['current_status'] === 'Accepted')
                                                <a href="{{ route('user.loa') }}?app_id={{ $app['id_application'] }}" class="btn btn-sm btn-success ms-1" title="Cetak Surat Penerimaan">
                                                    <i class="bi bi-award me-1"></i> LoA
                                                </a>
                                            @endif
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

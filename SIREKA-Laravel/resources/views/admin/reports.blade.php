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
                    <h5 class="fw-bold mb-0">Laporan & Statistik Rekrutmen</h5>
                    <small class="text-muted">Analisis data relasional, agregasi performa seleksi, dan penyerapan kandidat</small>
                </div>
            </div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary no-print">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- High Level Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0">
                        <div class="text-muted small fw-semibold text-uppercase">Total Pelamar</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $totalCandidates }}</div>
                        <small class="text-muted">Akun terdaftar</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0">
                        <div class="text-muted small fw-semibold text-uppercase">Total Lamaran</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ $totalApps }}</div>
                        <small class="text-muted">Masuk ke sistem</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0">
                        <div class="text-muted small fw-semibold text-uppercase">Rata-rata Match Score</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ number_format((float)($avgMatch ?? 0), 1) }}%</div>
                        <small class="text-muted">Kecocokan skill</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card-custom p-3 border-0">
                        <div class="text-muted small fw-semibold text-uppercase">Kandidat Diterima</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ $totalAccepted }}</div>
                        <small class="text-muted">Terbit LoA resmi</small>
                    </div>
                </div>
            </div>

            <!-- Table 1: Report by Job Vacancy -->
            <div class="card-custom p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-briefcase text-primary me-2"></i> Laporan Seleksi per Lowongan Pekerjaan
                    </h5>
                    <span class="badge bg-light text-muted border">Query GROUP BY j.id_job</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Posisi & Tipe</th>
                                <th>Divisi IT Penempatan</th>
                                <th class="text-center">Total Pelamar</th>
                                <th class="text-center">Rata-rata Match</th>
                                <th class="text-center">Interview</th>
                                <th class="text-center">Diterima (LoA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportJobs as $rj)
                                <tr>
                                    <td>
                                        <strong>{{ $rj['nama_job'] }}</strong>
                                        <small class="badge bg-light text-muted border d-block mt-0.5" style="width: fit-content;">{{ $rj['job_type'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                            <i class="bi bi-diagram-3 me-1"></i>{{ $rj['nama_divisi'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white fs-7">{{ $rj['total_applied'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format((float)($rj['avg_score'] ?? 0), 1) }}%</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $rj['interview_count'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $rj['accepted_count'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table 2: Report by Division -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-diagram-3 text-primary me-2"></i> Agregasi Rekrutmen per Divisi
                    </h5>
                    <span class="badge bg-light text-muted border">Query GROUP BY d.id_division</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Divisi</th>
                                <th>Perusahaan</th>
                                <th class="text-center">Total Lowongan</th>
                                <th class="text-center">Total Lamaran Masuk</th>
                                <th class="text-center">Kandidat Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportDivs as $rd)
                                <tr>
                                    <td><strong>{{ $rd['nama_divisi'] }}</strong></td>
                                    <td>{{ $rd['nama_company'] }}</td>
                                    <td class="text-center">{{ $rd['total_jobs'] }} Lowongan</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white">{{ $rd['total_applied'] }} Lamaran</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $rd['total_accepted'] }} Diterima</span>
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

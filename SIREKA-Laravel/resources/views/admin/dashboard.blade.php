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
                    <h5 class="fw-bold mb-0">Executive Dashboard HR</h5>
                    <small class="text-muted">SIREKA Central Recruitment & Talent Management System</small>
                </div>
            </div>
            <!-- Tombol proses rekrutmen hanya untuk HR; admin hanya melihat ringkasan -->
            @if (hasRole('hr'))
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.jobs') }}?action=create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Buka Lowongan Baru
                </a>
            </div>
            @endif
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Metrics Summary -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Total Lowongan</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-dark">{{ $totalJobs }}</span>
                            <div class="bg-primary-light text-primary rounded-3 p-2">
                                <i class="bi bi-briefcase fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Total Pelamar</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-dark">{{ $totalCandidates }}</span>
                            <div class="bg-primary-light text-primary rounded-3 p-2">
                                <i class="bi bi-people fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Lamaran Masuk</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-primary">{{ $totalApplications }}</span>
                            <div class="bg-primary-light text-primary rounded-3 p-2">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Sedang Diproses</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-warning">{{ $inProcess }}</span>
                            <div class="bg-warning-subtle text-warning rounded-3 p-2">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Interview Terjadwal</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-info">{{ $interviewsScheduled }}</span>
                            <div class="bg-info-subtle text-info rounded-3 p-2">
                                <i class="bi bi-calendar-event fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <div class="card-custom p-3 border-0 h-100">
                        <div class="text-muted small fw-semibold text-uppercase">Diterima (LoA)</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="fs-3 fw-bold text-success">{{ $acceptedCount }}</span>
                            <div class="bg-success-subtle text-success rounded-3 p-2">
                                <i class="bi bi-award fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart.js Visualizations -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart text-primary me-2"></i> Distribusi Status Lamaran</h5>
                            <span class="badge bg-light text-muted border">Live DB Grouping</span>
                        </div>
                        <div style="position: relative; height: 260px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i> Lamaran per Divisi Terpopuler</h5>
                            <span class="badge bg-light text-muted border">Relational Query JOIN</span>
                        </div>
                        <div style="position: relative; height: 260px;">
                            <canvas id="divisionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Lamaran Masuk Terbaru</h5>
                        <small class="text-muted">Daftar kandidat yang membutuhkan peninjauan HR</small>
                    </div>
                    @if (hasRole('hr'))
                    <a href="{{ route('admin.applications') }}" class="btn btn-sm btn-outline-primary">
                        Semua Lamaran <i class="bi bi-arrow-right"></i>
                    </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi & Divisi IT</th>
                                <th>Tanggal Apply</th>
                                <th>Match Score</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentApps as $app)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $app['nama_kandidat'] }}</div>
                                        <small class="text-muted">{{ $app['email_kandidat'] }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $app['nama_job'] }}</div>
                                        <small class="text-muted"><i class="bi bi-diagram-3 me-1"></i>{{ $app['nama_divisi'] }}</small>
                                    </td>
                                    <td class="small text-muted">
                                        {{ formatTanggalIndo($app['applied_at']) }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress-match flex-grow-1" style="width: 60px;">
                                                <div class="progress-bar {{ $app['match_score'] >= 80 ? 'bg-success' : ($app['match_score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $app['match_score'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $app['match_score'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        {!! getStatusBadge($app['current_status']) !!}
                                    </td>
                                    <td class="text-end">
                                        @if (hasRole('hr'))
                                        <a href="{{ route('admin.application_detail') }}?id={{ $app['id_application'] }}" class="btn btn-sm btn-primary">
                                            Proses Seleksi
                                        </a>
                                        @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Status Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusLabels) !!},
            datasets: [{
                data: {!! json_encode($statusTotals) !!},
                backgroundColor: ['#3b82f6', '#0ea5e9', '#64748b', '#f59e0b', '#eab308', '#06b6d4', '#10b981', '#ef4444', '#8b5cf6'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { family: "'Plus Jakarta Sans'" } } }
            }
        }
    });

    // 2. Division Chart
    const ctxDiv = document.getElementById('divisionChart').getContext('2d');
    new Chart(ctxDiv, {
        type: 'bar',
        data: {
            labels: {!! json_encode($divLabels) !!},
            datasets: [{
                label: 'Jumlah Lamaran',
                data: {!! json_encode($divTotals) !!},
                backgroundColor: '#1e40af',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection

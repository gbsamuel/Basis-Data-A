@extends('layouts.app')

@section('content')
@include('partials.navbar')

<div class="bg-corporate-blue py-5 text-white">
    <div class="container py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white text-primary">{{ $job['job_type'] }}</span>
                    @if ($job['status'] === 'Open')
                        <span class="badge bg-success text-white"><i class="bi bi-record-circle me-1"></i>Lowongan Dibuka</span>
                    @else
                        <span class="badge bg-danger text-white"><i class="bi bi-lock-fill me-1"></i>Pendaftaran Ditutup</span>
                    @endif
                </div>
                <h1 class="fw-bold text-white mb-1">{{ $job['nama_job'] }}</h1>
                <div class="text-light opacity-90 fs-5">
                    <i class="bi bi-building me-1"></i> {{ $job['nama_company'] }} &bull; 
                    <i class="bi bi-diagram-3 me-1 ms-2"></i> {{ $job['nama_divisi'] }}
                </div>
            </div>
            <div>
                @if ($job['status'] !== 'Open')
                    <button class="btn btn-light text-secondary fw-semibold px-4 py-2" disabled>
                        <i class="bi bi-lock-fill me-1"></i> Pendaftaran Ditutup
                    </button>
                @elseif (!$user)
                    <a href="{{ route('auth.login') }}?redirect=job_detail?id={{ $jobId }}" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login untuk Melamar
                    </a>
                @elseif (hasRole(['hr', 'admin']))
                    <span class="badge bg-secondary p-2">Mode Admin / HR</span>
                @elseif ($alreadyApplied)
                    <div class="d-flex gap-2">
                        <button class="btn btn-secondary px-4 py-2" disabled>
                            <i class="bi bi-check-circle-fill me-1"></i> Sudah Melamar
                        </button>
                        <a href="{{ route('user.tracking') }}?id={{ $applicationId }}" class="btn btn-warning text-dark fw-bold px-3 py-2 shadow-sm">
                            <i class="bi bi-clock-history me-1"></i> Lacak Status
                        </a>
                    </div>
                @elseif ($isClosed)
                    <button class="btn btn-secondary px-4 py-2.5" disabled>
                        <i class="bi bi-lock me-1"></i> Pendaftaran Ditutup
                    </button>
                @else
                    <a href="{{ route('user.apply') }}?job_id={{ $jobId }}" class="btn btn-warning text-dark fw-bold px-4 py-2.5 fs-6 shadow">
                        <i class="bi bi-send-check me-1"></i> Lamar Sekarang (Apply Now)
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    {!! renderFlash() !!}
    <div class="row g-4">
        <!-- Main Job Details -->
        <div class="col-lg-8">
            <div class="card-custom p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i> Deskripsi Pekerjaan</h4>
                <p class="text-secondary" style="white-space: pre-line;">{{ $job['deskripsi'] }}</p>

                <hr class="my-4">

                <h4 class="fw-bold mb-3"><i class="bi bi-list-check text-primary me-2"></i> Tanggung Jawab (Responsibilities)</h4>
                <p class="text-secondary" style="white-space: pre-line;">{{ $job['responsibilities'] }}</p>

                <hr class="my-4">

                <h4 class="fw-bold mb-3"><i class="bi bi-person-lines-fill text-primary me-2"></i> Persyaratan (Requirements)</h4>
                <p class="text-secondary" style="white-space: pre-line;">{{ $job['requirements'] }}</p>
            </div>

            <!-- Skill Matching Breakdown Card -->
            <div class="card-custom p-4 mb-4 border-primary-subtle">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-bullseye text-primary me-2"></i> Kebutuhan Keahlian & Match Score</h4>
                        <small class="text-muted">Kecocokan dihitung secara relasional dari data profil keahlian Anda.</small>
                    </div>
                    @if ($userNik)
                        <div class="text-end">
                            <span class="fs-4 fw-extrabold text-primary">{{ $skillsDetail['score'] }}%</span>
                            <span class="d-block small text-muted">Kecocokan</span>
                        </div>
                    @endif
                </div>

                @if ($userNik)
                    <div class="progress-match mb-4" style="height: 12px;">
                        <div class="progress-bar {{ $skillsDetail['score'] >= 80 ? 'bg-success' : ($skillsDetail['score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                             style="width: {{ $skillsDetail['score'] }}%"></div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($skillsDetail['skills'] as $sk)
                        @if ($userNik)
                            <span class="badge {{ $sk['matched'] ? 'bg-success text-white' : 'bg-light text-muted border' }} p-2 d-inline-flex align-items-center gap-1.5 fs-7">
                                <i class="bi {{ $sk['matched'] ? 'bi-check-circle-fill' : 'bi-x-circle' }}"></i>
                                {{ $sk['nama_skill'] }}
                                @if ($sk['matched'])
                                    <small class="text-white opacity-75">(Cocok)</small>
                                @endif
                            </span>
                        @else
                            <span class="badge bg-light text-primary border p-2 fs-7">
                                <i class="bi bi-tag-fill me-1"></i> {{ $sk['nama_skill'] }}
                            </span>
                        @endif
                    @endforeach
                </div>

                @if (!$userNik)
                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i> <a href="{{ route('auth.login') }}" class="fw-bold">Login sebagai Pelamar</a> untuk melihat persentase kecocokan keahlian Anda terhadap lowongan ini secara otomatis.
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="col-lg-4">
            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Ringkasan Pekerjaan</h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-briefcase me-2"></i> Tipe:</span>
                        <span class="fw-bold">{{ $job['job_type'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-laptop me-2"></i> Sistem Kerja:</span>
                        <span class="fw-bold">{{ $job['sistem_kerja'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-mortarboard me-2"></i> Pendidikan:</span>
                        <span class="fw-bold">Minimal {{ $job['min_pendidikan'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-clock-history me-2"></i> Pengalaman:</span>
                        <span class="fw-bold">{{ $job['experience_requirement'] }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-cash me-2"></i> Rentang Gaji:</span>
                        <span class="fw-bold text-success">
                            {{ formatRupiah($job['salary_min']) }} - {{ formatRupiah($job['salary_max']) }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i> Batas Lamaran:</span>
                        <span class="fw-bold text-danger">{{ formatTanggalIndo($job['deadline']) }}</span>
                    </li>
                </ul>

                <div class="mt-4 pt-3 border-top">
                    @if ($job['status'] !== 'Open')
                        <button class="btn btn-secondary w-100 py-2" disabled>
                            <i class="bi bi-lock-fill me-1"></i> Pendaftaran Ditutup
                        </button>
                        <small class="text-muted text-center d-block mt-2">Lowongan ini tidak menerima lamaran baru.</small>
                    @elseif (!$user)
                        <a href="{{ route('auth.login') }}?redirect=job_detail?id={{ $jobId }}" class="btn btn-primary w-100 py-2">
                            Login untuk Melamar
                        </a>
                    @elseif ($alreadyApplied)
                        <a href="{{ route('user.tracking') }}?id={{ $applicationId }}" class="btn btn-warning w-100 py-2 text-dark fw-bold">
                            <i class="bi bi-clock-history me-1"></i> Pantau Proses Lamaran
                        </a>
                    @elseif ($isClosed)
                        <button class="btn btn-secondary w-100 py-2" disabled>Pendaftaran Ditutup</button>
                    @elseif (!hasRole(['hr', 'admin']))
                        <a href="{{ route('user.apply') }}?job_id={{ $jobId }}" class="btn btn-primary w-100 py-2 fw-bold">
                            Lamar Sekarang
                        </a>
                    @endif
                </div>
            </div>

            <!-- Company Info Card -->
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Tentang Perusahaan</h5>
                <h6 class="fw-bold text-primary mb-1">{{ $job['nama_company'] }}</h6>
                <p class="text-muted small mb-2">{{ $job['industri'] }}</p>
                <div class="small text-muted mb-2">
                    <i class="bi bi-geo-alt me-1 text-danger"></i> {{ $job['alamat_company'] }}
                </div>
                <div class="small text-muted mb-2">
                    <i class="bi bi-envelope me-1 text-primary"></i> {{ $job['email_corporate'] }}
                </div>
                <div class="small text-muted">
                    <i class="bi bi-telephone me-1 text-success"></i> {{ $job['telp_company'] }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

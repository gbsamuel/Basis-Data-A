@extends('layouts.app')

@section('content')
@include('partials.navbar')

<!-- Hero Section (With Modern Dynamic Moving Building Background) -->
<section class="hero-building-section py-5 text-white position-relative overflow-hidden">
    <div class="hero-building-bg" id="heroBuildingBg" aria-hidden="true"></div>
    <div class="hero-building-overlay" aria-hidden="true"></div>
    <div class="container py-lg-5 text-center hero-building-content">
        <div class="row justify-content-center">
            <div class="col-lg-9 reveal-fade-up">
                <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill fw-bold shadow-sm animate-soft-float">
                    <i class="bi bi-cpu-fill text-primary me-1"></i> Official Tech Career & ATS Portal
                </span>
                <h1 class="display-4 fw-extrabold mb-3 text-white">
                    Bangun Karier Teknologi Masa Depanmu Bersama {{ $companyName }}
                </h1>
                <p class="lead mb-4 text-light opacity-90 fs-5 mx-auto" style="max-width: 680px;">
                    Bergabunglah bersama tim engineer kami. Kami membuka kesempatan berkarier di bidang <strong>Software Engineering</strong>, <strong>Data & AI</strong>, <strong>Cloud DevOps</strong>, dan <strong>Cybersecurity</strong> dengan seleksi transparan.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="{{ route('jobs') }}" class="btn btn-warning text-dark fw-bold px-4 py-3 rounded-3 shadow">
                        <i class="bi bi-search me-2"></i> Eksplorasi Lowongan IT
                    </a>
                    @if (!$user)
                        <a href="{{ route('auth.login') }}" class="btn btn-outline-light px-4 py-3 rounded-3 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Akun
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="btn btn-light text-primary px-4 py-3 rounded-3 fw-semibold">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard Pelamar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Available Jobs -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2 reveal-fade-up">
            <div>
                <h2 class="fw-bold mb-1">Lowongan Pekerjaan IT Terbaru</h2>
                <p class="text-muted mb-0">Eksplorasi posisi Full-Time, Magang, Management Trainee, dan PKL di {{ $companyName }}.</p>
            </div>
            <a href="{{ route('jobs') }}" class="btn btn-outline-primary">
                Lihat Semua Lowongan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach ($featuredJobs as $job)
                @php
                $matchScore = null;
                if ($userNik) {
                    $matchScore = calculateMatchScore($userNik, $job['id_job']);
                }
                
@endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card-custom h-100 p-4 d-flex flex-column card-hover position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                {{ $job['job_type'] }}
                            </span>
                            @if ($matchScore !== null)
                                <span class="score-badge {{ $matchScore >= 80 ? 'score-high' : ($matchScore >= 50 ? 'score-medium' : 'score-low') }}" title="Kecocokan Skill Anda">
                                    <i class="bi bi-bullseye"></i> {{ $matchScore }}% Match
                                </span>
                            @endif
                        </div>
                        <h5 class="fw-bold mb-1">
                            <a href="{{ route('job_detail') }}?id={{ $job['id_job'] }}" class="text-dark text-decoration-none">
                                {{ $job['nama_job'] }}
                            </a>
                        </h5>
                        <div class="text-muted small mb-2 fw-semibold">
                            <i class="bi bi-diagram-3-fill text-primary me-1"></i> Divisi: {{ $job['nama_divisi'] }}
                        </div>
                        <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $job['deskripsi'] }}
                        </p>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                <i class="bi bi-laptop me-1"></i> {{ $job['sistem_kerja'] }}
                            </div>
                            <a href="{{ route('job_detail') }}?id={{ $job['id_job'] }}" class="btn btn-sm btn-primary">
                                Detail Lowongan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-light border-top">
    <div class="container text-center py-4 reveal-scale">
        <h2 class="fw-bold mb-3">Siap Menjadi Bagian dari Inovasi Teknologi Kami?</h2>
        <p class="text-muted mb-4" style="max-width: 550px; margin: 0 auto;">
            Daftarkan diri Anda, lengkapi profil keahlian teknis Anda, dan ajukan lamaran ke posisi impian Anda.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('auth.register') }}" class="btn btn-primary px-4 py-2 fw-bold">Daftar Akun Pelamar</a>
            <a href="{{ route('helpdesk') }}" class="btn btn-outline-success px-4 py-2 fw-semibold">
                <i class="bi bi-whatsapp me-1"></i> Hubungi Helpdesk
            </a>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('content')
@include('partials.navbar')

<!-- Header halaman: label kecil, judul dua warna, dan subjudul (gaya sama dengan Tim Kami) -->
<!-- Foto latar bisa diganti di atribut style di bawah (url gambar) -->
<div class="jobs-hero py-5" style="background-image: url('https://picsum.photos/id/0/1600/600');">
    <div class="container py-4 text-center">
        <span class="section-label">Karier</span>
        <h1 class="section-title">Bergabung dengan <span class="text-accent">Tim Kami</span></h1>
        <p class="section-subtitle">Kami selalu mencari talenta yang ingin berkembang secara pribadi dan profesional bersama {{ $companyName }}.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Card -->
    <div class="card-custom p-4 mb-4 border-0 shadow-sm">
        <form method="GET" action="{{ route('jobs') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Cari Posisi / Keahlian / Lokasi</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="cth: Backend, Data Analyst, Cloud..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Divisi Teknologi</label>
                <select name="division" id="filter_division" class="form-select">
                    <option value="">Semua Divisi IT</option>
                    @foreach ($divisions as $d)
                        <option value="{{ $d['id_division'] }}" {{ $divisionId === (int)$d['id_division'] ? 'selected' : '' }}>
                            {{ $d['nama_divisi'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tipe Pekerjaan</label>
                <select name="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="Kerja" {{ $jobType === 'Kerja' ? 'selected' : '' }}>Kerja (Full-Time)</option>
                    <option value="Magang" {{ $jobType === 'Magang' ? 'selected' : '' }}>Magang (Internship)</option>
                    <option value="Management Trainee" {{ $jobType === 'Management Trainee' ? 'selected' : '' }}>Management Trainee (MT)</option>
                    <option value="PKL" {{ $jobType === 'PKL' ? 'selected' : '' }}>PKL (Praktik Kerja Lapangan)</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('jobs') }}" class="btn btn-outline-secondary">Reset Filter</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- Jobs Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted fw-semibold">Menampilkan <strong>{{ count($jobs) }}</strong> lowongan pekerjaan teknologi</span>
        @if ($userNik)
            <span class="badge bg-light text-primary border"><i class="bi bi-person-check me-1"></i> Match score dihitung otomatis dengan profil skill Anda</span>
        @endif
    </div>

    @if (empty($jobs))
        <div class="card-custom p-5 text-center my-4">
            <div class="display-1 text-muted mb-3"><i class="bi bi-search"></i></div>
            <h4 class="fw-bold">Tidak ada lowongan yang sesuai kriteria</h4>
            <p class="text-muted">Coba ubah kata kunci pencarian atau reset filter untuk melihat lowongan lainnya.</p>
            <div>
                <a href="{{ route('jobs') }}" class="btn btn-primary">Lihat Semua Lowongan</a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach ($jobs as $job)
                @php
                $matchScore = null;
                if ($userNik) {
                    $matchScore = calculateMatchScore($userNik, $job['id_job']);
                }
                
@endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card-custom h-100 p-4 d-flex flex-column card-hover position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                    {{ $job['job_type'] }}
                                </span>
                                @if ($job['status'] === 'Open')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-record-circle me-1"></i>Buka
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        <i class="bi bi-lock-fill me-1"></i>Tutup
                                    </span>
                                @endif
                            </div>
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
                        <div class="pt-3 border-top d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-laptop me-1"></i> {{ $job['sistem_kerja'] }}</span>
                                <span><i class="bi bi-cash-stack me-1"></i> {{ formatRupiah($job['salary_min']) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <small class="text-muted">Deadline: {{ formatTanggalIndo($job['deadline']) }}</small>
                                @if ($job['deadline'] < date('Y-m-d'))
                                    <span class="badge bg-secondary ms-1">Ditutup</span>
                                @endif
                                <a href="{{ route('job_detail') }}?id={{ $job['id_job'] }}" class="btn btn-sm btn-primary">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

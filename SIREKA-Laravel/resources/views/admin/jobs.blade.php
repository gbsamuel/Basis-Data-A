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
                    <h5 class="fw-bold mb-0">Manajemen Lowongan Pekerjaan</h5>
                    <small class="text-muted">Kelola posisi, prasyarat, gaji, dan mapping keahlian (Skill Match)</small>
                </div>
            </div>
            @if ($action === 'list')
                <a href="{{ route('admin.jobs') }}?action=create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Buka Lowongan Baru
                </a>
            @else
                <a href="{{ route('admin.jobs') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            @endif
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            @if ($action === 'create' || $action === 'edit')
                <!-- Form Create / Edit Job -->
                <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">
                        <i class="bi bi-briefcase text-primary me-2"></i>
                        {{ $editId > 0 ? 'Edit Lowongan Pekerjaan' : 'Buka Lowongan Pekerjaan Baru' }}
                    </h5>

                    <form method="POST" action="{{ route('admin.jobs') }}" class="row g-3">
@csrf
                        <input type="hidden" name="action_save_job" value="1">
                        <input type="hidden" name="id_job" value="{{ $editId }}">

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Judul Posisi Lowongan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_job" required class="form-control" placeholder="Contoh: Senior Backend Engineer" value="{{ $jobEdit['nama_job'] ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tipe Pekerjaan <span class="text-danger">*</span></label>
                            <select name="job_type" class="form-select" required>
                                <option value="Kerja" {{ ($jobEdit['job_type'] ?? '') === 'Kerja' ? 'selected' : '' }}>Kerja (Full-Time)</option>
                                <option value="Magang" {{ ($jobEdit['job_type'] ?? '') === 'Magang' ? 'selected' : '' }}>Magang (Internship)</option>
                                <option value="Management Trainee" {{ ($jobEdit['job_type'] ?? '') === 'Management Trainee' ? 'selected' : '' }}>Management Trainee (MT)</option>
                                <option value="PKL" {{ ($jobEdit['job_type'] ?? '') === 'PKL' ? 'selected' : '' }}>PKL (Praktik Kerja Lapangan)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status Lowongan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Open" {{ ($jobEdit['status'] ?? '') === 'Open' ? 'selected' : '' }}>Open (Buka)</option>
                                <option value="Closed" {{ ($jobEdit['status'] ?? '') === 'Closed' ? 'selected' : '' }}>Closed (Tutup)</option>
                                <option value="Draft" {{ ($jobEdit['status'] ?? '') === 'Draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Divisi IT Penempatan <span class="text-danger">*</span></label>
                            <select name="id_division" class="form-select" required>
                                <option value="">Pilih Divisi IT Penempatan...</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div['id_division'] }}" {{ ($jobEdit['id_division'] ?? 0) == $div['id_division'] ? 'selected' : '' }}>
                                        {{ $div['nama_divisi'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Sistem Kerja <span class="text-danger">*</span></label>
                            <select name="sistem_kerja" class="form-select" required>
                                @foreach (['Onsite', 'Hybrid', 'Remote'] as $sk)
                                    <option value="{{ $sk }}" {{ ($jobEdit['sistem_kerja'] ?? 'Onsite') === $sk ? 'selected' : '' }}>{{ $sk }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Batas Akhir (Deadline) <span class="text-danger">*</span></label>
                            <input type="date" name="deadline" required class="form-control" value="{{ $jobEdit['deadline'] ?? date('Y-m-d', strtotime('+30 days')) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Pendidikan Minimal</label>
                            <select name="min_pendidikan" class="form-select">
                                @foreach (jenjangOptions() as $jenjang)
                                    <option value="{{ $jenjang }}" {{ ($jobEdit['min_pendidikan'] ?? 'S1') === $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Durasi (bulan, untuk Magang/PKL)</label>
                            <input type="number" name="durasi_bulan" min="1" max="24" class="form-control" placeholder="cth: 3" value="{{ (string)($jobEdit['durasi_bulan'] ?? '') }}">
                        </div>

                        @if (isKepalaHr())
                            <!-- Hanya kepala HR yang bisa memilih / mengganti PIC -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">PIC Lowongan (HR penanggung jawab)</label>
                                <select name="pic_nik" class="form-select">
                                    <option value="">Belum ada PIC</option>
                                    @foreach ($hrList as $hr)
                                        <option value="{{ $hr['nik'] }}" {{ ($jobEdit['pic_nik'] ?? $myNik) === $hr['nik'] ? 'selected' : '' }}>
                                            {{ $hr['nama'] }} ({{ $hr['jabatan'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Pengalaman Minimal</label>
                            <input type="text" name="experience_requirement" class="form-control" placeholder="cth: 1-2 Tahun / Fresh Graduate" value="{{ $jobEdit['experience_requirement'] ?? '1-2 Tahun' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Gaji Minimal (Rp)</label>
                            <input type="number" name="salary_min" class="form-control" placeholder="cth: 8000000" value="{{ (string)($jobEdit['salary_min'] ?? '') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Gaji Maksimal (Rp)</label>
                            <input type="number" name="salary_max" class="form-control" placeholder="cth: 12000000" value="{{ (string)($jobEdit['salary_max'] ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" rows="3" required class="form-control" placeholder="Jelaskan gambaran umum peran dan posisi ini...">{{ $jobEdit['deskripsi'] ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggung Jawab (Responsibilities) <span class="text-danger">*</span></label>
                            <textarea name="responsibilities" rows="4" required class="form-control" placeholder="Poin-poin tanggung jawab tugas harian...">{{ $jobEdit['responsibilities'] ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kualifikasi & Persyaratan (Requirements) <span class="text-danger">*</span></label>
                            <textarea name="requirements" rows="4" required class="form-control" placeholder="Kriteria latar belakang, soft skill, dan hard skill...">{{ $jobEdit['requirements'] ?? '' }}</textarea>
                        </div>

                        <!-- Required Skills Checklist for Junction Table JOB_SKILL -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-primary">
                                        <i class="bi bi-stars text-warning me-1"></i> Mapping Keahlian Wajib (Junction Table: JOB_SKILL)
                                    </label>
                                    <small class="text-muted">Pilih keahlian yang menjadi acuan kalkulasi Match Score pelamar.</small>
                                </div>
                                <div class="row g-2">
                                    @foreach ($allSkills as $s)
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="form-check p-2 bg-white rounded border">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="skills[]" value="{{ $s['id_skill'] }}" id="sk_{{ $s['id_skill'] }}" {{ in_array($s['id_skill'], $jobSkillIds) ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="sk_{{ $s['id_skill'] }}">
                                                    {{ $s['nama_skill'] }}
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $s['category'] }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.jobs') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan & Terbitkan Lowongan
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Filter: semua lowongan atau hanya lowongan yang saya pegang -->
                <div class="d-flex gap-2 mb-3">
                    <a href="{{ route('admin.jobs') }}" class="btn btn-sm {{ $onlyMine ? 'btn-outline-primary' : 'btn-primary' }}">Semua Lowongan</a>
                    <a href="{{ route('admin.jobs') }}?mine=1" class="btn btn-sm {{ $onlyMine ? 'btn-primary' : 'btn-outline-primary' }}">Lowongan Saya (PIC)</a>
                </div>

                <!-- Table of Jobs -->
                <div class="card-custom p-4 border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi Pekerjaan</th>
                                    <th>Divisi IT</th>
                                    <th>Tipe</th>
                                    <th>PIC</th>
                                    <th>Gaji</th>
                                    <th>Keahlian</th>
                                    <th>Pelamar</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobs as $j)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $j['nama_job'] }}</div>
                                            <small class="text-muted"><i class="bi bi-laptop me-1"></i> {{ $j['sistem_kerja'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                                <i class="bi bi-diagram-3 me-1"></i>{{ $j['nama_divisi'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-light text-primary">{{ $j['job_type'] }}</span>
                                            @if (!empty($j['durasi_bulan']))
                                                <small class="text-muted d-block">{{ (int)$j['durasi_bulan'] }} bulan</small>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if ($j['pic_nik'])
                                                {{ $j['nama_pic'] }}
                                                @if ($j['pic_nik'] === $myNik)<span class="badge bg-success ms-1">Saya</span>@endif
                                            @else
                                                <span class="badge bg-danger">Belum ada PIC</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            {{ formatRupiah($j['salary_min']) }} - {{ formatRupiah($j['salary_max']) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border">{{ $j['total_skills'] }} Skill</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.applications') }}?job_id={{ $j['id_job'] }}" class="badge bg-primary text-white text-decoration-none">
                                                {{ $j['total_applicants'] }} Pelamar
                                            </a>
                                        </td>
                                        @php
 $bisaKelola = isKepalaHr() || $j['pic_nik'] === $myNik; 
@endphp
                                        <td>
                                            @if ($bisaKelola)
                                                <a href="{{ route('admin.jobs') }}?toggle_status={{ $j['status'] }}&id={{ $j['id_job'] }}" class="badge {{ $j['status'] === 'Open' ? 'bg-success' : 'bg-secondary' }} text-decoration-none" title="Klik untuk ubah status">
                                                    {{ $j['status'] }}
                                                </a>
                                            @else
                                                <span class="badge {{ $j['status'] === 'Open' ? 'bg-success' : 'bg-secondary' }}">{{ $j['status'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($bisaKelola)
                                                <a href="{{ route('admin.jobs') }}?action=edit&id={{ $j['id_job'] }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('admin.jobs') }}?delete={{ $j['id_job'] }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus lowongan pekerjaan ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            @else
                                                <span class="text-muted small">Hanya lihat</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection

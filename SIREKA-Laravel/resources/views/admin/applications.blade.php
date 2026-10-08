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
                    <h5 class="fw-bold mb-0">Manajemen Lamaran Masuk</h5>
                    <small class="text-muted">Proses seleksi, verifikasi berkas, dan pembaharuan tahapan rekrutmen</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Filter Controls -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="{{ route('admin.applications') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari kandidat atau posisi..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="job_id" class="form-select form-select-sm">
                            <option value="">Semua Lowongan</option>
                            @foreach ($jobsList as $jl)
                                <option value="{{ $jl['id_job'] }}" {{ $jobFilter === (int)$jl['id_job'] ? 'selected' : '' }}>
                                    {{ $jl['nama_job'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Applied" {{ $statusFilter === 'Applied' ? 'selected' : '' }}>Applied</option>
                            <option value="HR Review" {{ $statusFilter === 'HR Review' ? 'selected' : '' }}>HR Review</option>
                            <option value="Document Screening" {{ $statusFilter === 'Document Screening' ? 'selected' : '' }}>Document Screening</option>
                            <option value="Interview Scheduling" {{ $statusFilter === 'Interview Scheduling' ? 'selected' : '' }}>Interview Scheduling</option>
                            <option value="Interview" {{ $statusFilter === 'Interview' ? 'selected' : '' }}>Interview</option>
                            <option value="Final Decision" {{ $statusFilter === 'Final Decision' ? 'selected' : '' }}>Final Decision</option>
                            <option value="Accepted" {{ $statusFilter === 'Accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="Rejected" {{ $statusFilter === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="mine" value="1" id="onlyMine" {{ $onlyMine ? 'checked' : '' }}>
                            <label class="form-check-label small" for="onlyMine">Lowongan saya</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                        <a href="{{ route('admin.applications') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table of Applications -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-semibold">Menemukan <strong>{{ count($applications) }}</strong> berkas lamaran</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi & Divisi IT</th>
                                <th>Tgl Apply</th>
                                <th>Match Score</th>
                                <th>Status Tahapan</th>
                                <th>Berkas CV</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($applications))
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Tidak ada data lamaran yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            @else
                                @foreach ($applications as $app)
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
                                                <div class="progress-match flex-grow-1" style="width: 65px;">
                                                    <div class="progress-bar {{ $app['match_score'] >= 80 ? 'bg-success' : ($app['match_score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $app['match_score'] }}%"></div>
                                                </div>
                                                <span class="small fw-bold">{{ $app['match_score'] }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            {!! getStatusBadge($app['current_status']) !!}
                                            @if ($app['has_interview'] > 0)
                                                <span class="badge bg-warning text-dark mt-1 d-block" style="font-size: 0.68rem;">
                                                    <i class="bi bi-calendar-check me-1"></i> Interview Terjadwal
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ url('/') }}/uploads/cv/{{ $app['cv_file'] }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> CV
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            @if (isKepalaHr() || $app['pic_nik'] === $myNik)
                                                <a href="{{ route('admin.application_detail') }}?id={{ $app['id_application'] }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-sliders me-1"></i> Review & Status
                                                </a>
                                            @else
                                                <!-- Bukan PIC: hanya bisa melihat -->
                                                <a href="{{ route('admin.application_detail') }}?id={{ $app['id_application'] }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye me-1"></i> Lihat
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

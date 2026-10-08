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
                    <h5 class="fw-bold mb-0">Jadwal Sesi Wawancara (Interview)</h5>
                    <small class="text-muted">Informasi jadwal wawancara, lokasi, dan tautan pertemuan virtual</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            @if (empty($interviews))
                <div class="card-custom p-5 text-center border-0 shadow-sm">
                    <div class="display-4 text-muted mb-2"><i class="bi bi-calendar2-x"></i></div>
                    <h5 class="fw-bold">Belum Ada Jadwal Interview</h5>
                    <p class="text-muted small">Jadwal wawancara akan muncul di sini setelah lamaran Anda lolos tahap Document Screening.</p>
                    <a href="{{ route('user.applications') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> Pantau Status Lamaran
                    </a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($interviews as $itw)
                        <div class="col-lg-6">
                            <div class="card-custom h-100 p-4 border-0 shadow-sm position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary-light text-primary">{{ $itw['job_type'] }}</span>
                                    <span class="badge {{ $itw['status'] === 'Scheduled' ? 'bg-warning text-dark' : ($itw['status'] === 'Completed' ? 'bg-success' : 'bg-secondary') }}">
                                        {{ $itw['status'] }}
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $itw['nama_job'] }}</h5>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i> {{ $itw['nama_company'] }} &bull; {{ $itw['nama_divisi'] }}
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-3 small border">
                                    <div class="mb-2">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i> <strong>Tanggal:</strong> {{ formatTanggalIndo($itw['tanggal']) }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-clock me-2 text-primary"></i> <strong>Waktu:</strong> {{ substr($itw['waktu'], 0, 5) }} WIB
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-person-badge me-2 text-primary"></i> <strong>Pewawancara:</strong> {{ $itw['nama_interviewer'] }} ({{ $itw['jabatan_interviewer'] ?? 'HR' }})
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-laptop me-2 text-primary"></i> <strong>Tipe:</strong> {{ $itw['type'] }}
                                    </div>
                                    @if ($itw['type'] === 'Offline' && !empty($itw['location']))
                                        <div>
                                            <i class="bi bi-geo-alt me-2 text-danger"></i> <strong>Lokasi:</strong> {{ $itw['location'] }}
                                        </div>
                                    @endif
                                </div>

                                @if (!empty($itw['meeting_link']))
                                    <div class="d-grid mb-3">
                                        <a href="{{ $itw['meeting_link'] }}" target="_blank" class="btn btn-success fw-bold">
                                            <i class="bi bi-camera-video me-1"></i> Buka Tautan Meeting Virtual
                                        </a>
                                    </div>
                                @endif

                                @if (!empty($itw['notes']))
                                    <div class="small text-muted border-top pt-2">
                                        <strong>Catatan HR:</strong> {!! nl2br(e($itw['notes'])) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</div>
@endsection

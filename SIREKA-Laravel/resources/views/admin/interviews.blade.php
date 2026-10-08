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
                    <h5 class="fw-bold mb-0">Manajemen Jadwal Wawancara (Interview)</h5>
                    <small class="text-muted">Pantau jadwal wawancara teknis, user, dan HR bersama kandidat</small>
                </div>
            </div>
            <a href="{{ route('admin.applications') }}?status=Document+Screening" class="btn btn-sm btn-primary">
                <i class="bi bi-calendar-plus me-1"></i> Jadwalkan dari Lamaran
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi & Perusahaan</th>
                                <th>Tanggal & Waktu</th>
                                <th>Pewawancara</th>
                                <th>Tipe / Link</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($interviews))
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Belum ada jadwal interview yang dibuat.
                                    </td>
                                </tr>
                            @else
                                @foreach ($interviews as $itw)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $itw['nama_kandidat'] }}</div>
                                            <small class="text-muted">{{ $itw['telp_kandidat'] }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $itw['nama_job'] }}</div>
                                            <small class="text-muted">{{ $itw['nama_company'] }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ formatTanggalIndo($itw['tanggal']) }}</strong>
                                            <small class="text-muted d-block">{{ substr($itw['waktu'], 0, 5) }} WIB</small>
                                        </td>
                                        <td>
                                            {{ $itw['nama_interviewer'] }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $itw['type'] }}</span>
                                            @if (!empty($itw['meeting_link']))
                                                <a href="{{ $itw['meeting_link'] }}" target="_blank" class="small d-block text-primary text-truncate" style="max-width: 150px;">
                                                    <i class="bi bi-link me-1"></i> Link Meeting
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $itw['status'] === 'Scheduled' ? 'bg-warning text-dark' : ($itw['status'] === 'Completed' ? 'bg-success' : 'bg-secondary') }}">
                                                {{ $itw['status'] }}
                                            </span>
                                            @if ($itw['nilai'] !== null)
                                                <small class="d-block text-muted">Nilai {{ (int)$itw['nilai'] }} &bull; {{ $itw['rekomendasi'] }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if (isKepalaHr() || $itw['pic_nik'] === $myNik)
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('admin.interviews') }}?set_status=Completed&id={{ $itw['id_interview'] }}">Tandai Selesai (Completed)</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.interviews') }}?set_status=Cancelled&id={{ $itw['id_interview'] }}">Batalkan (Cancelled)</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.interviews') }}?set_status=Scheduled&id={{ $itw['id_interview'] }}">Jadwalkan Ulang (Scheduled)</a></li>
                                                </ul>
                                            </div>
                                            @endif
                                            <a href="{{ route('admin.application_detail') }}?id={{ $itw['id_application'] }}" class="btn btn-sm btn-primary ms-1">
                                                Dossier
                                            </a>
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

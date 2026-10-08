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
                    <h5 class="fw-bold mb-0">Manajemen Letter of Acceptance (LoA)</h5>
                    <small class="text-muted">Daftar surat penerimaan kerja resmi yang telah diterbitkan sistem</small>
                </div>
            </div>
            <a href="{{ route('admin.applications') }}?status=Accepted" class="btn btn-sm btn-primary">
                <i class="bi bi-file-earmark-check me-1"></i> Pelamar Diterima
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
                                <th>No. Surat LoA</th>
                                <th>Kandidat Penerima</th>
                                <th>Posisi & Divisi IT</th>
                                <th>Disahkan Oleh</th>
                                <th>Tgl Terbit</th>
                                <th>Tgl Mulai Kerja</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($loas))
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Belum ada surat penerimaan (LoA) yang diterbitkan.
                                    </td>
                                </tr>
                            @else
                                @foreach ($loas as $l)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $l['loa_number'] }}</strong>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $l['nama_kandidat'] }}</div>
                                            <small class="text-muted">NIK: {{ $l['nik'] }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $l['position'] }}</div>
                                            <span class="badge bg-primary-light text-primary border border-primary-subtle">{{ $l['division'] }}</span>
                                        </td>
                                        <td>
                                            <small class="text-dark fw-semibold">{{ $l['authorized_by'] }}</small>
                                        </td>
                                        <td class="small text-muted">
                                            {{ formatTanggalIndo($l['issue_date']) }}
                                        </td>
                                        <td class="small text-dark fw-bold">
                                            {{ formatTanggalIndo($l['join_date']) }}
                                        </td>
                                        <td>
                                            <!-- Jawaban pelamar: Issued (belum dijawab), Accepted, Declined -->
                                            <span class="badge {{ $l['status'] === 'Accepted' ? 'bg-success' : ($l['status'] === 'Declined' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $l['status'] === 'Issued' ? 'Menunggu Jawaban' : ($l['status'] === 'Accepted' ? 'Diterima' : 'Ditolak') }}
                                            </span>
                                            @if (!empty($l['responded_at']))
                                                <small class="d-block text-muted">{{ formatTanggalIndo($l['responded_at']) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('user.loa') }}?app_id={{ $l['id_application'] }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Surat">
                                                <i class="bi bi-printer me-1"></i> Cetak PDF
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

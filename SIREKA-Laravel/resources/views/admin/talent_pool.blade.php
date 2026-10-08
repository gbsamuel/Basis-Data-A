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
                    <h5 class="fw-bold mb-0">Bank Bakat & Kandidat Potensial (Talent Pool)</h5>
                    <small class="text-muted">Kelola kandidat berkualitas untuk kebutuhan rekrutmen masa depan</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Concept Card -->
            <div class="card-custom p-3 mb-4 bg-light border-purple shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-purple text-white rounded-3 p-2 px-3">
                        <i class="bi bi-stars fs-3"></i>
                    </div>
                    <div>
                        <strong class="text-dark">Talent Pool Database System:</strong>
                        <p class="text-muted small mb-0">
                            Kandidat yang tidak diterima pada batch seleksi karena keterbatasan kuota tetap diarsipkan di sini. Saat perusahaan membuka lowongan baru, HR dapat langsung mencari kandidat dari Talent Pool tanpa memulai seleksi dari nol.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="{{ route('admin.talent_pool') }}" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari keahlian (SQL, Python), nama kandidat..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status Talent Pool</option>
                            <option value="Available" {{ $statusFilter === 'Available' ? 'selected' : '' }}>Available (Siap Dihubungi)</option>
                            <option value="Considered" {{ $statusFilter === 'Considered' ? 'selected' : '' }}>Considered (Sedang Dipertimbangkan)</option>
                            <option value="Hired" {{ $statusFilter === 'Hired' ? 'selected' : '' }}>Hired (Sudah Direkrut)</option>
                            <option value="Inactive" {{ $statusFilter === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="{{ route('admin.talent_pool') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                    <div class="col text-md-end small text-muted">
                        Total: <strong>{{ count($talents) }}</strong> Kandidat
                    </div>
                </form>
            </div>

            <!-- Talent Pool Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi Referensi</th>
                                <th>Keahlian Terdata</th>
                                <th>Alasan Pengarsipan</th>
                                <th>Tgl Dimasukkan</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($talents))
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Tidak ada kandidat di dalam Talent Pool sesuai filter.
                                    </td>
                                </tr>
                            @else
                                @foreach ($talents as $tp)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $tp['nama_kandidat'] }}</div>
                                            <small class="text-muted">{{ $tp['pendidikan_terakhir'] }} &bull; {{ $tp['email'] }}</small>
                                        </td>
                                        <td>
                                            @if (!empty($tp['nama_job']))
                                                <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                                    <i class="bi bi-briefcase me-1"></i>{{ $tp['nama_job'] }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border">Talenta Direct</span>
                                            @endif
                                        </td>
                                        <td style="max-width: 250px;">
                                            <span class="small text-dark fw-semibold">
                                                {{ $tp['skills_list'] ?? 'Belum ada skill' }}
                                            </span>
                                        </td>
                                        <td class="small text-secondary" style="max-width: 250px;">
                                            {{ $tp['reason'] }}
                                        </td>
                                        <td class="small text-muted">
                                            {{ formatTanggalIndo($tp['added_at']) }}
                                            <small class="d-block">oleh {{ $tp['nama_hr'] ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $tp['status'] === 'Available' ? 'bg-success' : ($tp['status'] === 'Considered' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                {{ $tp['status'] }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Kelola
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('admin.talent_pool') }}?update_status=Considered&id={{ $tp['id_talent_pool'] }}">Pertimbangkan Posisi Baru (Considered)</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.talent_pool') }}?update_status=Hired&id={{ $tp['id_talent_pool'] }}">Tandai Telah Direkrut (Hired)</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.talent_pool') }}?update_status=Available&id={{ $tp['id_talent_pool'] }}">Set Available</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.talent_pool') }}?update_status=Inactive&id={{ $tp['id_talent_pool'] }}">Tidak Aktif (Inactive)</a></li>
                                                </ul>
                                            </div>
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

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
                    <h5 class="fw-bold mb-0">Kelola Akun</h5>
                    <small class="text-muted">Kelola akun pelamar dan HR, serta buat akun HR baru</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHrModal">
                <i class="bi bi-person-plus me-1"></i> Tambah Akun HR
            </button>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Ringkasan jumlah akun -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">Pelamar</small>
                        <h3 class="fw-bold mb-0">{{ (int)$counts['user'] }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">HR</small>
                        <h3 class="fw-bold mb-0 text-primary">{{ (int)$counts['hr'] }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">Admin</small>
                        <h3 class="fw-bold mb-0">{{ (int)$counts['admin'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="{{ route('admin.users') }}" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama, email, atau NIK..." value="{{ $search }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select form-select-sm">
                            <option value="">Semua Role</option>
                            <option value="user" {{ $roleFilter === 'user' ? 'selected' : '' }}>Pelamar</option>
                            <option value="hr" {{ $roleFilter === 'hr' ? 'selected' : '' }}>HR</option>
                            <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Tabel akun -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Role</th>
                                <th>Jabatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($accounts))
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Tidak ada akun yang sesuai filter.</td>
                                </tr>
                            @endif
                            @foreach ($accounts as $acc)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $acc['nama'] }}</div>
                                        <small class="text-muted">NIK: {{ $acc['nik'] }}</small>
                                    </td>
                                    <td class="small">{{ $acc['email'] }}</td>
                                    <td class="small">{{ $acc['no_telepon'] }}</td>
                                    <td>
                                        <span class="badge {{ $roleBadges[$acc['role']] }}">{{ $roleLabels[$acc['role']] }}</span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $acc['position'] ?? '-' }}
                                        @if (!empty($acc['is_kepala_hr']))
                                            <span class="badge bg-warning text-dark ms-1">Kepala HR</span>
                                        @endif
                                        @if ($acc['role'] === 'hr')
                                            <div class="small">PIC {{ (int)$acc['jumlah_pic'] }} lowongan</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <!-- Akun admin dan akun sendiri tidak bisa dihapus -->
                                        @if ($acc['role'] === 'hr')
                                            <form method="POST" action="{{ route('admin.users') }}" class="d-inline">
@csrf
                                                <input type="hidden" name="action_toggle_kepala" value="1">
                                                <input type="hidden" name="nik" value="{{ $acc['nik'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    {{ !empty($acc['is_kepala_hr']) ? 'Cabut Kepala HR' : 'Jadikan Kepala HR' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($acc['role'] !== 'admin')
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="openResetModal('{{ $acc['nik'] }}', {{ json_encode($acc['nama']) }})">
                                                <i class="bi bi-key"></i> Reset Password
                                            </button>
                                        @endif
                                        @if ($acc['role'] !== 'admin' && $acc['nik'] !== $me['nik'])
                                            @php
                                            // Pesan konfirmasi berbeda untuk HR yang masih memegang lowongan
                                            $pesanHapus = 'Hapus akun ' . $acc['nama'] . '? Semua data yang terhubung juga akan terhapus.';
                                            if ($acc['role'] === 'hr' && $acc['jumlah_pic'] > 0) {
                                                $pesanHapus = $acc['nama'] . ' masih menjadi PIC ' . $acc['jumlah_pic'] . ' lowongan. Lowongan tersebut akan menjadi tanpa PIC. Tetap hapus?';
                                            }
                                            
@endphp
                                            <form method="POST" action="{{ route('admin.users') }}" class="d-inline"
                                                  onsubmit="return confirm({{ json_encode($pesanHapus) }});">
@csrf
                                                <input type="hidden" name="action_delete" value="1">
                                                <input type="hidden" name="nik" value="{{ $acc['nik'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
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

<!-- Modal Tambah Akun HR -->
<div class="modal fade" id="addHrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users') }}">
@csrf
                <input type="hidden" name="action_add_hr" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Tambah Akun HR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control" required minlength="16" maxlength="16" pattern="[0-9]{16}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">No. Telepon <span class="text-danger">*</span></label>
                        <input type="tel" name="no_telepon" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="jabatan" class="form-control" value="HR Recruiter">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_kepala_hr" id="isKepalaHr" value="1">
                            <label class="form-check-label small" for="isKepalaHr">Jadikan Kepala HR (bisa mengatur PIC dan memproses semua lowongan)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun HR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users') }}">
@csrf
                <input type="hidden" name="action_reset_password" value="1">
                <input type="hidden" name="nik" id="resetNik">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-key me-1"></i> Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small mb-3">Akun: <strong id="resetNama"></strong></p>
                    <label class="form-label small fw-semibold">Password Sementara <span class="text-danger">*</span></label>
                    <input type="text" name="new_password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                    <small class="text-muted">Berikan password ini kepada pemilik akun, lalu minta ia menggantinya di halaman Profil.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Isi data akun ke modal reset password, lalu tampilkan modalnya
    function openResetModal(nik, nama) {
        document.getElementById('resetNik').value = nik;
        document.getElementById('resetNama').innerText = nama;
        new bootstrap.Modal(document.getElementById('resetModal')).show();
    }
</script>
@endsection

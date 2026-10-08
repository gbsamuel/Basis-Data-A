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
                    <h5 class="fw-bold mb-0">Manajemen Divisi IT</h5>
                    <small class="text-muted">Kelola struktur departemen dan divisi teknologi perusahaan internal</small>
                </div>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#divisionModal" onclick="resetForm()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Divisi IT
            </button>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Divisi IT</th>
                                <th>Deskripsi & Ruang Lingkup</th>
                                <th class="text-center">Lowongan Terkait</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($divisions))
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data divisi IT.</td>
                                </tr>
                            @else
                                @foreach ($divisions as $d)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                <i class="bi bi-diagram-3 text-primary"></i>
                                                <span>{{ $d['nama_divisi'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted" style="max-width: 450px; display: block;">
                                                {{ $d['deskripsi'] ?? 'Divisi teknologi internal.' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <!-- Klik untuk membuka/menutup daftar lowongan divisi ini di bawah baris -->
                                            <button type="button" class="badge bg-primary-light text-primary border-0 px-3 py-2"
                                                    onclick="toggleJobs({{ $d['id_division'] }})">
                                                <i class="bi bi-briefcase me-1"></i> {{ $d['total_jobs'] }} Lowongan
                                                <i class="bi bi-chevron-down ms-1" id="jobsIcon{{ $d['id_division'] }}"></i>
                                            </button>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick='editDivision({!! json_encode($d) !!})' title="Edit Divisi">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <a href="{{ route('admin.divisions') }}?delete={{ $d['id_division'] }}" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus divisi ini? Seluruh data lowongan terkait akan ikut terhapus.')" 
                                                   title="Hapus Divisi">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Baris tambahan berisi daftar lowongan divisi ini (awalnya disembunyikan) -->
                                    <tr id="jobsRow{{ $d['id_division'] }}" class="d-none">
                                        <td colspan="4" class="bg-light px-4 py-3">
                                            @if (empty($jobsByDivision[$d['id_division']]))
                                                <span class="text-muted small">Belum ada lowongan di divisi ini.</span>
                                            @else
                                                @foreach ($jobsByDivision[$d['id_division']] as $job)
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 border-bottom">
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $job['nama_job'] }}</div>
                                                            <small class="text-muted">
                                                                {{ $job['job_type'] }} &bull;
                                                                {{ $job['sistem_kerja'] }} &bull;
                                                                Batas: {{ formatTanggalIndo($job['deadline']) }}
                                                            </small>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge {{ $job['status'] === 'Open' ? 'bg-success' : 'bg-secondary' }}">{{ $job['status'] }}</span>
                                                            <!-- Ganti PIC (cadangan untuk admin) -->
                                                            <form method="POST" action="{{ route('admin.divisions') }}" class="d-flex gap-1">
@csrf
                                                                <input type="hidden" name="action_set_pic" value="1">
                                                                <input type="hidden" name="id_job" value="{{ $job['id_job'] }}">
                                                                <select name="pic_nik" class="form-select form-select-sm" style="width: 170px;">
                                                                    <option value="">Belum ada PIC</option>
                                                                    @foreach ($hrList as $hr)
                                                                        <option value="{{ $hr['nik'] }}" {{ $job['pic_nik'] === $hr['nik'] ? 'selected' : '' }}>{{ $hr['nama'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
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

<!-- Modal Form Divisi -->
<div class="modal fade" id="divisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.divisions') }}">
@csrf
                <input type="hidden" name="action_save_division" value="1">
                <input type="hidden" name="id_division" id="form_id_division" value="0">
                
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Divisi IT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Divisi IT <span class="text-danger">*</span></label>
                        <input type="text" name="nama_divisi" id="form_nama" class="form-control" placeholder="cth: Cloud & DevOps Engineering" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi / Tugas Pokok</label>
                        <textarea name="deskripsi" id="form_deskripsi" rows="3" class="form-control" placeholder="Jelaskan fokus teknis departemen ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Divisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Tambah Divisi IT';
    document.getElementById('form_id_division').value = '0';
    document.getElementById('form_nama').value = '';
    document.getElementById('form_deskripsi').value = '';
}

function editDivision(d) {
    document.getElementById('modalTitle').innerText = 'Edit Divisi IT: ' + d.nama_divisi;
    document.getElementById('form_id_division').value = d.id_division;
    document.getElementById('form_nama').value = d.nama_divisi;
    document.getElementById('form_deskripsi').value = d.deskripsi || '';
    
    var modal = new bootstrap.Modal(document.getElementById('divisionModal'));
    modal.show();
}
</script>

<script>
    // Buka/tutup baris daftar lowongan di bawah divisi yang diklik
    function toggleJobs(idDivision) {
        const row = document.getElementById('jobsRow' + idDivision);
        const icon = document.getElementById('jobsIcon' + idDivision);
        row.classList.toggle('d-none');
        icon.className = row.classList.contains('d-none') ? 'bi bi-chevron-down ms-1' : 'bi bi-chevron-up ms-1';
    }
</script>
@endsection

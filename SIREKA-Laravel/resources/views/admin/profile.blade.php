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
                    <h5 class="fw-bold mb-0">Profil Saya</h5>
                    <small class="text-muted">Kelola foto, data diri, dan kata sandi akun Anda</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="row g-4">
                <div class="col-lg-7">
                    <!-- Foto Profil -->
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-camera-fill text-primary me-2"></i> Foto Profil
                        </h5>
                        <form method="POST" action="{{ route('admin.profile') }}" enctype="multipart/form-data"
                              class="d-flex flex-column flex-sm-row align-items-center gap-4">
@csrf
                            <input type="hidden" name="action_upload_photo" value="1">

                            @if (!empty($user['profile_photo']))
                                <img src="{{ url('/') }}/uploads/profiles/{{ $user['profile_photo'] }}"
                                     id="photoPreview" class="profile-photo-lg" alt="Foto profil">
                            @else
                                <img src="" id="photoPreview" class="profile-photo-lg d-none" alt="Foto profil">
                                <div id="photoInitial" class="profile-photo-lg profile-photo-initial">
                                    {{ strtoupper(substr($user['nama'] ?? 'A', 0, 1)) }}
                                </div>
                            @endif

                            <div class="flex-grow-1 w-100">
                                <!-- Tampilan biasa -->
                                <div id="photoInfo">
                                    <h6 class="fw-bold mb-0">{{ $user['nama'] }}</h6>
                                    <small class="text-muted d-block">{{ $user['email'] }}</small>
                                    <span class="badge bg-primary-light text-primary my-2">{{ $roleLabel }}</span><br>
                                    <button type="button" id="photoEditBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-camera me-1"></i>
                                        {{ empty($user['profile_photo']) ? 'Tambah Foto' : 'Ganti Foto' }}
                                    </button>
                                </div>

                                <!-- Form upload, muncul setelah tombol diklik -->
                                <div id="photoForm" class="d-none">
                                    <label class="form-label small fw-semibold">Pilih foto baru</label>
                                    <input type="file" name="profile_photo" id="photoInput" class="form-control mb-2"
                                           accept=".jpg,.jpeg,.png,.webp" required>
                                    <small class="text-muted d-block mb-2">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i> Simpan Foto
                                    </button>
                                    <button type="button" id="photoCancelBtn" class="btn btn-light btn-sm">Batal</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Data Diri -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i> Data Diri
                        </h5>
                        <form method="POST" action="{{ route('admin.profile') }}" class="row g-3">
@csrf
                            <input type="hidden" name="action_update_profile" value="1">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NIK</label>
                                <input type="text" class="form-control" value="{{ $user['nik'] }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" class="form-control" value="{{ $user['email'] }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Role</label>
                                <input type="text" class="form-control" value="{{ $roleLabel }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jabatan</label>
                                <input type="text" class="form-control" value="{{ $jabatan }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="{{ $user['nama'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="no_telepon" class="form-control" required value="{{ $user['no_telepon'] }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ganti Kata Sandi -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-key-fill text-primary me-2"></i> Ganti Kata Sandi
                        </h5>
                        <form method="POST" action="{{ route('admin.profile') }}" class="vstack gap-3">
@csrf
                            <input type="hidden" name="action_change_password" value="1">
                            <div>
                                <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div>
                                <label class="form-label small fw-semibold">Ulangi Kata Sandi Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-outline-primary">Perbarui Kata Sandi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Tombol "Ganti Foto": sembunyikan info, tampilkan form upload
    document.getElementById('photoEditBtn').addEventListener('click', function () {
        document.getElementById('photoInfo').classList.add('d-none');
        document.getElementById('photoForm').classList.remove('d-none');
    });

    // Tombol "Batal": muat ulang halaman agar kembali ke tampilan biasa
    document.getElementById('photoCancelBtn').addEventListener('click', function () {
        window.location.reload();
    });

    // Pratinjau foto sebelum disimpan
    document.getElementById('photoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const preview = document.getElementById('photoPreview');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');

        const initial = document.getElementById('photoInitial');
        if (initial) initial.classList.add('d-none');
    });
</script>
@endsection

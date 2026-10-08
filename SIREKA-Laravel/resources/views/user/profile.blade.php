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
                    <h5 class="fw-bold mb-0">Profil & Manajemen Keahlian</h5>
                    <small class="text-muted">Kelola data pribadi dan katalog keahlian untuk meningkatkan Match Score</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="row g-4">
                <!-- Biodata Form -->
                <div class="col-lg-7">
                    <!-- Foto Profil -->
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-camera-fill text-primary me-2"></i> Foto Profil
                        </h5>
                        <form method="POST" action="{{ route('user.profile') }}" enctype="multipart/form-data"
                              class="d-flex flex-column flex-sm-row align-items-center gap-4">
@csrf
                            <input type="hidden" name="action_upload_photo" value="1">

                            <!-- Tampilkan foto jika ada, jika belum ada tampilkan huruf awal nama -->
                            @if (!empty($user['profile_photo']))
                                <img src="{{ url('/') }}/uploads/profiles/{{ $user['profile_photo'] }}"
                                     id="photoPreview" class="profile-photo-lg" alt="Foto profil">
                            @else
                                <img src="" id="photoPreview" class="profile-photo-lg d-none" alt="Foto profil">
                                <div id="photoInitial" class="profile-photo-lg profile-photo-initial">
                                    {{ strtoupper(substr($user['nama'] ?? 'U', 0, 1)) }}
                                </div>
                            @endif

                            <div class="flex-grow-1 w-100">
                                <!-- Tampilan biasa: nama, email, dan tombol untuk membuka form -->
                                <div id="photoInfo">
                                    <h6 class="fw-bold mb-0">{{ $user['nama'] }}</h6>
                                    <small class="text-muted d-block mb-3">{{ $user['email'] }}</small>
                                    <button type="button" id="photoEditBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-camera me-1"></i>
                                        {{ empty($user['profile_photo']) ? 'Tambah Foto' : 'Ganti Foto' }}
                                    </button>
                                </div>

                                <!-- Form upload, disembunyikan (d-none) sampai tombol di atas diklik -->
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

                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i> Biodata Pribadi
                        </h5>
                        <form method="POST" action="{{ route('user.profile') }}" class="row g-3">
@csrf
                            <input type="hidden" name="action_update_profile" value="1">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Induk Kependudukan (NIK)</label>
                                <input type="text" class="form-control" value="{{ $user['nik'] }}" disabled readonly>
                                <small class="text-muted">NIK bersifat permanen dan tidak dapat diubah.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alamat Email</label>
                                <input type="email" class="form-control" value="{{ $user['email'] }}" disabled readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="{{ $user['nama'] }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" name="no_telepon" class="form-control" required value="{{ $user['no_telepon'] }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" required value="{{ $user['tanggal_lahir'] }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                                <select name="jenjang_pendidikan" class="form-select" required>
                                    @foreach (jenjangOptions() as $jenjang)
                                        <option value="{{ $jenjang }}" {{ ($user['jenjang_pendidikan'] ?? '') === $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Status Pendidikan <span class="text-danger">*</span></label>
                                <select name="status_pendidikan" class="form-select" required>
                                    <option value="Lulus" {{ ($user['status_pendidikan'] ?? '') === 'Lulus' ? 'selected' : '' }}>Sudah Lulus</option>
                                    <option value="Masih Sekolah/Kuliah" {{ ($user['status_pendidikan'] ?? '') === 'Masih Sekolah/Kuliah' ? 'selected' : '' }}>Masih Sekolah/Kuliah</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Sekolah / Kampus <span class="text-danger">*</span></label>
                                <input type="text" name="institusi" class="form-control" required value="{{ $user['institusi'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="jurusan" class="form-control" required value="{{ $user['jurusan'] ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tahun Lulus / Perkiraan Lulus <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_lulus" class="form-control" required value="{{ (string)$user['tahun_lulus'] }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                                <textarea name="alamat" rows="2" class="form-control" required>{{ $user['alamat'] }}</textarea>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan Biodata
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-key text-primary me-2"></i> Ganti Kata Sandi
                        </h5>
                        <form method="POST" action="{{ route('user.profile') }}" class="row g-3">
@csrf
                            <input type="hidden" name="action_change_password" value="1">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" name="old_password" required class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                                <input type="password" name="new_password" minlength="6" required class="form-control" placeholder="Min. 6 karakter">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Ulangi Sandi Baru</label>
                                <input type="password" name="confirm_password" minlength="6" required class="form-control" placeholder="Konfirmasi">
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-outline-primary px-3">Update Kata Sandi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Skills Management Card (Relational USER_SKILL) -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-stars text-warning me-2"></i> Katalog Keahlian Anda
                            </h5>
                            <span class="badge bg-primary">{{ count($mySkills) }} Skill</span>
                        </div>

                        <p class="text-muted small">
                            Keahlian ini digunakan oleh sistem SIREKA untuk menghitung <strong>Match Score</strong> secara instan terhadap setiap lowongan.
                        </p>

                        <!-- Add Skill Form -->
                        <form method="POST" action="{{ route('user.profile') }}" class="p-3 bg-light rounded-3 border mb-3">
@csrf
                            <input type="hidden" name="action_add_skill" value="1">
                            <div class="fw-semibold small mb-2 text-dark">Tambah Keahlian Baru:</div>
                            <div class="row g-2">
                                <div class="col-7">
                                    <select name="id_skill" class="form-select form-select-sm" required>
                                        <option value="">Pilih Keahlian...</option>
                                        @foreach ($availableSkills as $as)
                                            <option value="{{ $as['id_skill'] }}">
                                                {{ $as['nama_skill'] }} ({{ $as['category'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="level" class="form-select form-select-sm">
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate" selected>Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                        <option value="Expert">Expert</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                        <i class="bi bi-plus-circle me-1"></i> Tambahkan Skill
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- List of User Skills -->
                        <div class="d-flex flex-column gap-2">
                            @if (empty($mySkills))
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-exclamation-circle fs-3 d-block mb-1"></i>
                                    Belum ada keahlian yang ditambahkan.<br>Tambahkan keahlian Anda untuk memaksimalkan peluang kerja.
                                </div>
                            @else
                                @foreach ($mySkills as $sk)
                                    <div class="p-2.5 bg-white rounded-3 border d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark d-block">{{ $sk['nama_skill'] }}</span>
                                            <small class="text-muted">{{ $sk['category'] }} &bull; <span class="badge bg-light text-primary border">{{ $sk['level'] }}</span></small>
                                        </div>
                                        <a href="{{ route('user.profile') }}?delete_skill={{ $sk['id_skill'] }}" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Hapus keahlian ini dari profil?')" title="Hapus Skill">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
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

    // Tombol "Batal": kembalikan ke tampilan biasa (muat ulang halaman agar pratinjau ikut kembali)
    document.getElementById('photoCancelBtn').addEventListener('click', function () {
        window.location.reload();
    });

    // Pratinjau foto sebelum disimpan
    document.getElementById('photoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const preview = document.getElementById('photoPreview');
        preview.src = URL.createObjectURL(file);   // buat alamat sementara untuk file yang dipilih
        preview.classList.remove('d-none');

        const initial = document.getElementById('photoInitial');
        if (initial) initial.classList.add('d-none');
    });
</script>
@endsection

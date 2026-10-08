@extends('layouts.app')

@section('content')
@include('partials.navbar')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 px-3 mb-2 shadow-sm">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Daftar Akun Pelamar</h3>
                    <p class="text-muted small">Bergabunglah dengan SIREKA untuk memulai perjalanan karier Anda</p>
                </div>

                @if (!empty($errors))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki kesalahan berikut:</div>
                        <ul class="mb-0 small ps-3">
                            @foreach ($errors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.register') }}" class="row g-3">
@csrf
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">NIK KTP (16 Digit) <span class="text-danger">*</span></label>
                        <input type="text" name="nik" maxlength="16" required class="form-control" placeholder="3201xxxxxxxxxxxx" value="{{ $formData['nik'] }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" required class="form-control" placeholder="Nama sesuai KTP" value="{{ $formData['nama'] }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" required class="form-control" placeholder="nama@email.com" value="{{ $formData['email'] }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor WhatsApp / Telepon <span class="text-danger">*</span></label>
                        <input type="tel" name="no_telepon" required class="form-control" placeholder="0812xxxxxxxx" value="{{ $formData['no_telepon'] }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" required class="form-control" value="{{ $formData['tanggal_lahir'] }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                        <select name="jenjang_pendidikan" required class="form-select">
                            <option value="">Pilih Jenjang</option>
                            @foreach (jenjangOptions() as $jenjang)
                                <option value="{{ $jenjang }}" {{ $formData['jenjang_pendidikan'] === $jenjang ? 'selected' : '' }}>{{ $jenjang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status Pendidikan <span class="text-danger">*</span></label>
                        <select name="status_pendidikan" required class="form-select">
                            <option value="Lulus" {{ $formData['status_pendidikan'] === 'Lulus' ? 'selected' : '' }}>Sudah Lulus</option>
                            <option value="Masih Sekolah/Kuliah" {{ $formData['status_pendidikan'] === 'Masih Sekolah/Kuliah' ? 'selected' : '' }}>Masih Sekolah/Kuliah (untuk Magang/PKL)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Sekolah / Kampus <span class="text-danger">*</span></label>
                        <input type="text" name="institusi" required class="form-control" placeholder="cth: SMK Negeri 1 Depok / Universitas Airlangga" value="{{ $formData['institusi'] }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" name="jurusan" required class="form-control" placeholder="cth: Teknik Informatika" value="{{ $formData['jurusan'] }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tahun Lulus / Perkiraan Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" min="1970" max="{{ date('Y') + 6 }}" required class="form-control" value="{{ (string)$formData['tahun_lulus'] }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" rows="2" required class="form-control" placeholder="Alamat lengkap tempat tinggal saat ini">{{ $formData['alamat'] }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password" minlength="6" required class="form-control" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" minlength="6" required class="form-control" placeholder="Ketik ulang kata sandi">
                    </div>

                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Selesaikan Pendaftaran
                        </button>
                    </div>

                    <div class="col-12 text-center small text-muted">
                        Sudah memiliki akun SIREKA? 
                        <a href="{{ route('auth.login') }}" class="fw-bold text-primary">Masuk ke Akun</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

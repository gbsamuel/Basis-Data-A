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
                    <h5 class="fw-bold mb-0">Pengaturan Sistem</h5>
                    <small class="text-muted">Kelola profil perusahaan dan konfigurasi sistem SIREKA</small>
                </div>
            </div>
            <a href="{{ route('about') }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Pratinjau Publik
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Ringkasan -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-primary-light text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ number_format($totalDivisi) }}</h4>
                            <small class="text-muted fw-semibold">Divisi IT</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ number_format($totalJobs) }}</h4>
                            <small class="text-muted fw-semibold">Total Lowongan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ number_format($totalAkun) }}</h4>
                            <small class="text-muted fw-semibold">Total Akun</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Bagian 1: Profil Perusahaan -->
                <div class="col-lg-7">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <h5 class="fw-bold mb-1"><i class="bi bi-building text-primary me-2"></i> Profil Perusahaan</h5>
                        <p class="text-muted small border-bottom pb-3 mb-3">Ditampilkan di halaman Tentang Kami, detail lowongan, dan kop surat LoA.</p>

                        <form method="POST" action="{{ route('admin.settings') }}" class="row g-3">
@csrf
                            <input type="hidden" name="action_save_profile" value="1">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Perusahaan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_company" class="form-control" required value="{{ $company['nama_company'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Sektor Industri <span class="text-danger">*</span></label>
                                <input type="text" name="industri" class="form-control" required value="{{ $company['industri'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Resmi Rekrutmen <span class="text-danger">*</span></label>
                                <input type="email" name="email_corporate" class="form-control" required value="{{ $company['email_corporate'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon Kantor</label>
                                <input type="text" name="no_telepon" class="form-control" value="{{ $company['no_telepon'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat Kantor Pusat</label>
                                <textarea name="alamat" rows="2" class="form-control">{{ $company['alamat'] ?? '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Deskripsi Perusahaan</label>
                                <textarea name="deskripsi" rows="4" class="form-control">{{ $company['deskripsi'] ?? '' }}</textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Simpan Profil Perusahaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bagian 2: Pengaturan Sistem -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <h5 class="fw-bold mb-1"><i class="bi bi-gear text-primary me-2"></i> Konfigurasi Sistem</h5>
                        <p class="text-muted small border-bottom pb-3 mb-3">Nama platform, kontak bantuan, dan pejabat pengesah LoA.</p>

                        <form method="POST" action="{{ route('admin.settings') }}" class="row g-3">
@csrf
                            <input type="hidden" name="action_save_settings" value="1">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nama Sistem / Platform</label>
                                <input type="text" name="platform_name" class="form-control" value="{{ $platform }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nomor WhatsApp Helpdesk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="helpdesk_whatsapp" class="form-control" value="{{ $wa }}" placeholder="Contoh: 6281234567890" required>
                                </div>
                                <small class="text-muted">Format internasional tanpa tanda plus.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Email Dukungan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                                    <input type="email" name="helpdesk_email" class="form-control" value="{{ $email }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Pejabat Pengesah LoA</label>
                                <input type="text" name="loa_authorized_signer" class="form-control" value="{{ $signer }}" required>
                                <small class="text-muted">Tercetak di bagian tanda tangan dokumen LoA.</small>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

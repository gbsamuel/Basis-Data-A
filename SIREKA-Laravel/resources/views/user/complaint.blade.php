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
                    <h5 class="fw-bold mb-0">Layanan Keluhan & Tiket Dukungan</h5>
                    <small class="text-muted">Kirimkan kendala teknis atau pertanyaan resmi seputar proses rekrutmen</small>
                </div>
            </div>
            <a href="{{ route('helpdesk') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Hotline
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="row g-4">
                <!-- Submit Form -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-pencil-square text-primary me-2"></i> Buat Tiket Baru
                        </h5>
                        <form method="POST" action="{{ route('user.complaint') }}">
@csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori Keluhan <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="Proses Seleksi">Proses Seleksi & Berkas</option>
                                    <option value="Interview Schedule">Jadwal Interview / Wawancara</option>
                                    <option value="Website & Teknis">Kendala Teknis Website</option>
                                    <option value="Letter of Acceptance">Letter of Acceptance (LoA)</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Subjek Keluhan <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" placeholder="Contoh: Kendala tautan Google Meet" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi Rinci <span class="text-danger">*</span></label>
                                <textarea name="description" rows="5" class="form-control" placeholder="Jelaskan secara rinci permasalahan yang Anda alami..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-send me-1"></i> Kirimkan Tiket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Complaints History List -->
                <div class="col-lg-7">
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-clock-history text-primary me-2"></i> Riwayat Tiket Anda
                        </h5>

                        @if (empty($complaints))
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-chat-left-check fs-2 d-block mb-2"></i>
                                Belum ada tiket keluhan yang dikirimkan.
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($complaints as $c)
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-secondary mb-1">{{ $c['category'] }}</span>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $c['subject'] }}</h6>
                                                <small class="text-muted">{{ date('d M Y, H:i', strtotime($c['created_at'])) }} WIB</small>
                                            </div>
                                            <span class="badge {{ $c['status'] === 'Resolved' ? 'bg-success' : ($c['status'] === 'In Review' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                                {{ $c['status'] }}
                                            </span>
                                        </div>
                                        <p class="text-secondary small mb-2">{!! nl2br(e($c['description'])) !!}</p>

                                        @if (!empty($c['admin_response']))
                                            <div class="p-2.5 bg-white rounded border-start border-success border-3 mt-2 small">
                                                <strong class="text-success d-block mb-1"><i class="bi bi-reply-fill me-1"></i> Tanggapan Tim Support HR:</strong>
                                                <span class="text-dark">{!! nl2br(e($c['admin_response'])) !!}</span>
                                                @if (!empty($c['resolved_at']))
                                                    <small class="text-muted d-block mt-1">Diselesaikan pada: {{ date('d M Y, H:i', strtotime($c['resolved_at'])) }} WIB</small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

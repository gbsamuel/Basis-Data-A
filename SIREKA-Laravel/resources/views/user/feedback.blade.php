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
                    <h5 class="fw-bold mb-0">Penilaian Pengalaman Rekrutmen</h5>
                    <small class="text-muted">Bantu kami meningkatkan kualitas layanan rekrutmen digital</small>
                </div>
            </div>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="row g-4 justify-content-center">
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm text-center">
                        <div class="bg-primary-light text-primary rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-chat-heart fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Bagaimana Pengalaman Anda?</h4>
                        <p class="text-muted small mb-4">
                            Berikan penilaian terhadap transparansi proses, kemudahan fitur Match Score, dan interaksi rekrutmen di SIREKA.
                        </p>

                        <form method="POST" action="{{ route('user.feedback') }}" class="text-start">
@csrf
                            <div class="mb-3 text-center">
                                <label class="form-label small fw-semibold d-block">Rating Kepuasan</label>
                                <div class="btn-group" role="group">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <input type="radio" class="btn-check" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ $i === 5 ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning" for="star{{ $i }}">
                                            {{ $i }} <i class="bi bi-star-fill text-warning"></i>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Ulasan & Masukan Anda <span class="text-danger">*</span></label>
                                <textarea name="message" rows="4" class="form-control" placeholder="Tuliskan pengalaman Anda dalam melamar, fitur apa yang paling membantu, atau saran perbaikan..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-send me-1"></i> Kirimkan Penilaian
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-stars text-warning me-2"></i> Ulasan yang Telah Anda Berikan
                        </h5>

                        @if (empty($myFeedbacks))
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                                Anda belum memberikan penilaian. Masukan Anda sangat berarti bagi kami!
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($myFeedbacks as $fb)
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                @for ($s = 1; $s <= 5; $s++)
                                                    <i class="bi bi-star-fill {{ $s <= $fb['rating'] ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                                @endfor
                                                <span class="fw-bold ms-1 text-dark">{{ $fb['rating'] }}/5 Bintang</span>
                                            </div>
                                            <small class="text-muted">{{ formatTanggalIndo($fb['created_at']) }}</small>
                                        </div>
                                        <p class="text-secondary small mb-0">{!! nl2br(e($fb['message'])) !!}</p>
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

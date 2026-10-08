<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'SIREKA - Sistem Informasi Rekrutmen & Kandidat' }}</title>

    <!-- Meta SEO -->
    <meta name="description" content="SIREKA - Platform Sistem Informasi Rekrutmen dan Pengelolaan Kandidat Terintegrasi dengan Smart Matching Score, Tracking Status, dan Talent Pool.">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Design System CSS -->
    <!-- ?v= berisi waktu terakhir file diubah, supaya browser selalu memuat CSS terbaru (tidak memakai cache lama) -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">

    <!-- Chart.js (Loaded for dashboard and reports) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Top Scroll Reading Progress Indicator -->
    <div id="scrollProgressBar" class="scroll-progress-bar" aria-hidden="true"></div>

@yield('content')

    <!-- Global Footer (tidak ditampilkan jika halaman mengisi $hideFooter = true, contoh: halaman login) -->
    @if (empty($hideFooter))
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-primary">SIREKA</span>
                <span class="text-muted">|</span>
                <span class="text-muted small">&copy; {{ date('Y') }} Sistem Informasi Rekrutmen & Kandidat. Project Basis Data.</span>
            </div>
            <div class="d-flex align-items-center gap-3 small text-muted">
                <span>Mendukung <strong class="text-primary">SDGs 8: Decent Work & Economic Growth</strong></span>
                <span>&bull;</span>
                <a href="{{ route('helpdesk') }}" class="text-muted">Helpdesk WhatsApp</a>
            </div>
        </div>
    </footer>
    @endif


    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Main JS -->
    <script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}"></script>
</body>
</html>

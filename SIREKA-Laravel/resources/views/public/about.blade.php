@extends('layouts.app')

@section('content')
@include('partials.navbar')

<!-- Hero Section -->
<div class="bg-corporate-blue py-5 text-white position-relative overflow-hidden">
    <!-- Foto gedung yang sama dengan hero di halaman Home, ditimpa lapisan biru -->
    <div class="hero-building-bg" aria-hidden="true"></div>
    <div class="hero-building-overlay" aria-hidden="true"></div>
    <div class="container py-4 position-relative" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    <i class="bi bi-cpu-fill me-1"></i> Dedicated Tech Career Portal
                </span>
                <h1 class="display-5 fw-bold text-white mb-3">
                    {{ $company['nama_company'] ?? 'PT Solusi Teknologi Nusantara' }}
                </h1>
                <p class="lead text-light opacity-90 mb-4" style="max-width: 680px;">
                    Pelopor inovasi rekayasa perangkat lunak enterprise, cloud-native architecture, dan kecerdasan buatan (Artificial Intelligence) untuk mempercepat transformasi digital Indonesia.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('jobs') }}" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                        <i class="bi bi-briefcase-fill me-1"></i> Jelajahi Lowongan IT
                    </a>
                    <a href="{{ route('helpdesk') }}" class="btn btn-outline-light px-4 py-2">
                        <i class="bi bi-chat-dots-fill me-1"></i> Kontak Tim Rekrutmen
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 shadow-sm text-start text-white">
                    <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-geo-alt-fill me-2"></i>Kantor Pusat</h6>
                    <p class="small text-light mb-2">{{ $company['alamat'] ?? 'Cyber 2 Tower Lt. 18, Jakarta Selatan' }}</p>
                    <hr class="border-white border-opacity-25 my-2">
                    <div class="small text-light mb-1"><i class="bi bi-envelope me-2"></i>{{ $company['email_corporate'] ?? 'career@solusiteknologi.co.id' }}</div>
                    <div class="small text-light"><i class="bi bi-telephone me-2"></i>{{ $company['no_telepon'] ?? '021-52901122' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- About Us: judul dan tagline di kiri, foto di kanan -->
<div class="container pt-5">
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="section-label">Tentang Kami</span>
            <h2 class="section-title">Membangun Teknologi untuk <span class="text-accent">Kemajuan Indonesia</span></h2>
            <p class="section-subtitle ms-0">
                Tim engineer, desainer, dan spesialis data yang membantu bisnis berkembang melalui perangkat lunak yang andal.
            </p>
        </div>
        <div class="col-lg-6">
            <img src="{{ aboutImageUrl($aboutPhotos['intro']) }}" class="about-rounded-img" alt="Tim perusahaan">
        </div>
    </div>

    <!-- Our Mission: foto di kiri, teks di kanan -->
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6 order-2 order-lg-1">
            <img src="{{ aboutImageUrl($aboutPhotos['mission']) }}" class="about-rounded-img" alt="Misi perusahaan">
        </div>
        <div class="col-lg-6 order-1 order-lg-2">
            <h3 class="fw-bold mb-3">Misi Kami: <span class="text-primary">Tumbuh Bersama Talenta dan Perusahaan</span></h3>
            <p class="text-muted">
                Kami percaya produk yang hebat lahir dari orang-orang yang hebat. Misi kami adalah membuka karier
                yang bermakna di bidang teknologi dan membuat proses rekrutmen yang adil, transparan, serta mudah
                diikuti oleh setiap kandidat. Hal ini mendukung SDGs 8: pekerjaan layak dan pertumbuhan ekonomi.
            </p>
        </div>
    </div>

    <!-- Our Story: teks di kiri, foto di kanan -->
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Cerita Kami</h3>
            <p class="text-muted">
                Semua berawal dari sekelompok kecil developer yang ingin membuat perangkat lunak untuk menyelesaikan
                masalah nyata bisnis di Indonesia. Dari satu kantor kecil di Jakarta, kami tumbuh menjadi perusahaan
                yang menghadirkan solusi cloud, data, dan AI untuk klien di seluruh Indonesia.
            </p>
            <p class="text-muted mb-0">
                Seiring tim yang terus bertambah, kami sadar bahwa merekrut orang yang tepat sama pentingnya dengan
                membangun produk yang tepat. Karena itulah kami membuat SIREKA, portal rekrutmen tempat setiap pelamar
                dapat memantau proses seleksinya, mulai dari lamaran pertama hingga penawaran kerja.
            </p>
        </div>
        <div class="col-lg-6">
            <img src="{{ aboutImageUrl($aboutPhotos['story']) }}" class="about-rounded-img" alt="Cerita perusahaan">
        </div>
    </div>
</div>

<!-- Values & Tech Culture -->
<div class="container py-5">
    <div class="text-center mb-5" id="values">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Our Engineering Values</span>
        <h2 class="fw-bold text-dark mt-1">Mengapa Membangun Karier Bersama Kami?</h2>
        <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
            Kami percaya bahwa produk teknologi kelas dunia lahir dari budaya kerja kolaboratif, kebebasan bereksperimen, dan apresiasi nyata terhadap talenta engineer.
        </p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-primary-light text-primary rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-laptop fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Modern Tech Stack</h5>
                <p class="text-muted small mb-0">
                    Bekerja dengan teknologi mutakhir: Cloud Kubernetes, microservices, Python AI, React, Docker, dan arsitektur data terdistribusi.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-success-subtle text-success rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-house-gear fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Fleksibilitas Hybrid</h5>
                <p class="text-muted small mb-0">
                    Mendukung keseimbangan kerja dan kehidupan melalui opsi kerja hybrid dan remote friendly dengan jam kerja berbasis hasil.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-warning-subtle text-warning rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-mortarboard fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Growth & Sertifikasi</h5>
                <p class="text-muted small mb-0">
                    Alokasi anggaran khusus untuk sertifikasi profesional internasional (AWS, Google Cloud, CKAD) dan akses materi pembelajaran tak terbatas.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-info-subtle text-info rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-check fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Seleksi Adil & Terbuka</h5>
                <p class="text-muted small mb-0">
                    Didukung fitur Smart Match Score dan Relational Timeline Tracking di portal SIREKA agar setiap tahapan seleksi Anda terpantau transparan.
                </p>
            </div>
        </div>
    </div>

    <!-- Tim Kami: kartu foto bulat, posisinya naik-turun (selang-seling) -->
    <div class="team-section mb-5" id="team">
        <div class="text-center mb-5">
            <span class="section-label">Our Team</span>
            <!-- Kata kedua dibungkus span text-accent agar berwarna biru -->
            <h2 class="section-title">Meet Our <span class="text-accent">Professional Team</span></h2>
            <p class="section-subtitle">
                Orang-orang di balik SIREKA yang merancang dan membangun sistem ini.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($teamMembers as $i => $member)
                <!-- Kartu urutan genap (ke-2 dan ke-4) diberi class team-card-offset agar posisinya turun -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="team-card {{ $i % 2 === 1 ? 'team-card-offset' : '' }}">
                        <div class="team-photo">
                            <img src="{{ aboutImageUrl($member['foto']) }}"
                                 alt="Foto {{ $member['nama'] }}">
                        </div>
                        <h6 class="fw-bold text-dark mb-0">{{ $member['nama'] }}</h6>
                        <div class="text-primary small fw-semibold mb-2">{{ $member['peran'] }}</div>
                        <p class="text-muted small mb-2">{{ $member['bio'] }}</p>
                        <div class="team-social">
                            <a href="{{ $member['instagram'] }}" title="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="{{ $member['linkedin'] }}" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                            <a href="{{ $member['github'] }}" title="GitHub"><i class="bi bi-github"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Galeri dinamis: kartu yang disorot (hover) atau diklik akan melebar -->
    <div class="mb-5">
        <div class="text-center mb-4">
            <span class="text-primary fw-bold text-uppercase small">Galeri</span>
            <h2 class="fw-bold text-dark mt-1">Suasana Kerja Kami</h2>
        </div>

        <div class="photo-gallery" id="photoGallery">
            @foreach ($galleryItems as $i => $item)
                <!-- Kartu pertama langsung aktif (lebar) saat halaman dibuka -->
                <div class="gallery-card {{ $i === 0 ? 'active' : '' }}"
                     style="background-image: url('{{ aboutImageUrl($item['foto']) }}');">
                    <div class="gallery-caption">
                        <h5 class="text-white fw-bold mb-1">{{ $item['judul'] }}</h5>
                        <p class="mb-0 small">{{ $item['keterangan'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Divisi IT Kami -->
    <div class="division-section mb-5">
        <div class="d-flex justify-content-between align-items-end mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-white mb-1">Divisi di Perusahaan Kami</h3>
                <p class="mb-0 text-light opacity-75">Kenali bidang pekerjaan yang ada di setiap divisi.</p>
            </div>
            <!-- Tombol geser kiri dan kanan -->
            <div class="d-flex gap-2">
                <button type="button" class="division-nav" id="divisionPrev" title="Geser ke kiri"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="division-nav" id="divisionNext" title="Geser ke kanan"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

        <!-- Satu baris kartu yang bisa digeser ke samping -->
        <div class="division-scroll" id="divisionScroll">
            @foreach ($divisions as $div)
                <div class="division-card">
                    <div class="division-icon"><i class="bi bi-diagram-3-fill"></i></div>
                    <h6 class="fw-bold text-white mb-2">{{ $div['nama_divisi'] }}</h6>
                    <p class="small mb-0">
                        {{ $div['deskripsi'] ?? 'Divisi rekayasa dan operasional teknologi internal.' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SIREKA by the Numbers: angka diambil dari database -->
    <div class="text-center mb-4">
        <span class="section-label">Pencapaian Kami</span>
        <h2 class="section-title">SIREKA <span class="text-accent">dalam Angka</span></h2>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="number-card">
                <div class="number-icon"><i class="bi bi-briefcase-fill"></i></div>
                <div class="number-value">{{ (int)$totalOpenJobs }}</div>
                <div class="text-muted mb-3">Lowongan Dibuka</div>
                <a href="{{ route('jobs') }}" class="fw-semibold">Selengkapnya <i class="bi bi-chevron-right small"></i></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="number-card">
                <div class="number-icon"><i class="bi bi-diagram-3-fill"></i></div>
                <div class="number-value">{{ (int)$totalDivisions }}</div>
                <div class="text-muted mb-3">Divisi Teknologi</div>
                <a href="{{ route('jobs') }}" class="fw-semibold">Selengkapnya <i class="bi bi-chevron-right small"></i></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="number-card">
                <div class="number-icon"><i class="bi bi-people-fill"></i></div>
                <div class="number-value">{{ (int)$totalApplicants }}</div>
                <div class="text-muted mb-3">Pelamar Terdaftar</div>
                <a href="{{ route('auth.register') }}" class="fw-semibold">Selengkapnya <i class="bi bi-chevron-right small"></i></a>
            </div>
        </div>
    </div>

    <!-- Panel penghargaan (contoh, silakan ganti sesuai kebutuhan) -->
    <div class="awards-panel mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-4 text-center text-lg-start">
                <h4 class="fw-bold mb-1">Diakui sebagai <span class="text-primary">Tempat Kerja Terbaik</span></h4>
                <p class="text-muted small mb-0">Penghargaan dan sertifikasi yang telah kami terima.</p>
            </div>
            <div class="col-lg-8">
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-end gap-3">
                    <div class="award-badge"><i class="bi bi-trophy-fill"></i><span>Perusahaan Teknologi Terbaik</span></div>
                    <div class="award-badge"><i class="bi bi-award-fill"></i><span>Tempat Kerja Unggulan</span></div>
                    <div class="award-badge"><i class="bi bi-patch-check-fill"></i><span>ISO 27001</span></div>
                    <div class="award-badge"><i class="bi bi-star-fill"></i><span>Budaya Kerja Positif</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Life at SIREKA: foto besar di kiri, panel biru di kanan -->
    <div class="life-section mb-5">
        <div class="life-photo" style="background-image: url('{{ aboutImageUrl($aboutPhotos['life']) }}');"></div>
        <div class="life-panel">
            <h2 class="life-title">Kehidupan di SIREKA</h2>
            <p class="mb-4">
                Budaya kerja kami dibangun di atas kolaborasi, rasa ingin tahu, dan saling menghargai. Setiap anggota
                tim didorong untuk berbagi ide, terus belajar, dan berkembang bersama perusahaan.
            </p>
            <a href="#values" class="life-link">Nilai dan Budaya Kami <i class="bi bi-chevron-right"></i></a>
            <a href="#team" class="life-link">Meet Our Team <i class="bi bi-chevron-right"></i></a>
            <a href="{{ route('jobs') }}" class="life-link">Bergabung dengan Tim Kami <i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <!-- Office & Contact Card -->
    <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-primary text-white mb-2">Headquarters & Tech Hub</span>
                <h3 class="fw-bold text-dark mb-3">{{ $company['nama_company'] }}</h3>
                <p class="text-muted mb-4">
                    {{ $company['deskripsi'] }}
                </p>
                <div class="vstack gap-2 text-muted small">
                    <div><i class="bi bi-geo-alt-fill text-danger me-2"></i>{{ $company['alamat'] }}</div>
                    <div><i class="bi bi-envelope-fill text-primary me-2"></i>{{ $company['email_corporate'] }}</div>
                    <div><i class="bi bi-telephone-fill text-success me-2"></i>{{ $company['no_telepon'] }}</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-primary-light rounded-4 p-4 text-center border border-primary-subtle">
                    <i class="bi bi-people-fill text-primary display-4 mb-2"></i>
                    <h5 class="fw-bold text-dark mb-2">Punya Pertanyaan Mengenai Rekrutmen?</h5>
                    <p class="text-muted small mb-3">
                        Tim Human Capital & Tech Recruiter kami siap membantu Anda melalui layanan tiket bantuan atau WhatsApp Helpdesk resmi.
                    </p>
                    <a href="{{ route('helpdesk') }}" class="btn btn-success fw-bold px-4 py-2">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi WhatsApp Helpdesk
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Galeri dinamis: kartu yang disorot mouse (atau diklik di HP) menjadi lebar
    const galleryCards = document.querySelectorAll('#photoGallery .gallery-card');

    function activateCard(card) {
        // Hapus class active dari semua kartu, lalu pasang di kartu yang dipilih
        galleryCards.forEach(function (c) {
            c.classList.remove('active');
        });
        card.classList.add('active');
    }

    galleryCards.forEach(function (card) {
        card.addEventListener('mouseenter', function () { activateCard(card); });
        card.addEventListener('click', function () { activateCard(card); });
    });

    // Kartu divisi: tombol panah menggeser baris sejauh 300px ke kiri/kanan
    const divisionScroll = document.getElementById('divisionScroll');
    document.getElementById('divisionPrev').addEventListener('click', function () {
        divisionScroll.scrollBy({ left: -300, behavior: 'smooth' });
    });
    document.getElementById('divisionNext').addEventListener('click', function () {
        divisionScroll.scrollBy({ left: 300, behavior: 'smooth' });
    });
</script>
@endsection

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
                    <h5 class="fw-bold mb-0">Application Tracking Timeline</h5>
                    <small class="text-muted">Pantau progres seleksi secara transparan dan terukur</small>
                </div>
            </div>
            <a href="{{ route('user.applications') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Lamaran
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <!-- Job & Application Header Summary -->
            <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary-light text-primary">{{ $app['job_type'] }}</span>
                            <span class="small text-muted">ID Lamaran: #APP-{{ str_pad($app['id_application'], 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ $app['nama_job'] }}</h3>
                        <div class="text-muted small">
                            <i class="bi bi-building me-1"></i> <strong>{{ $app['nama_company'] }}</strong> &bull; 
                            <i class="bi bi-diagram-3 me-1 ms-2"></i> {{ $app['nama_divisi'] }} &bull; 
                            <i class="bi bi-calendar-check me-1 ms-2"></i> Diajukan: {{ formatTanggalIndo($app['applied_at']) }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <span class="small text-muted d-block">Match Score Anda:</span>
                            <span class="fs-4 fw-extrabold text-primary">{{ $app['match_score'] }}%</span>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted d-block">Status Saat Ini:</span>
                            {!! getStatusBadge($currentStatus) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditional Acceptance Banner -->
            @if ($currentStatus === 'Accepted')
                <div class="card-custom p-4 mb-4 border-success bg-success-subtle shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <span class="badge bg-success text-white mb-2">PENGUMUMAN RESMI</span>
                            <h4 class="fw-bold text-success mb-1">Selamat! Anda Dinyatakan Diterima</h4>
                            <p class="text-dark small mb-0">
                                Selamat bergabung di <strong>{{ $app['nama_company'] }}</strong> sebagai <strong>{{ $app['nama_job'] }}</strong>. Dokumen Surat Penerimaan Kerja Resmi (Letter of Acceptance) telah diterbitkan.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('user.loa') }}?app_id={{ $app['id_application'] }}" class="btn btn-success fw-bold px-4 py-2.5 shadow">
                                <i class="bi bi-award-fill me-1"></i> Buka & Cetak LoA
                            </a>
                        </div>
                    </div>
                </div>
            @elseif ($currentStatus === 'Rejected')
                <div class="card-custom p-4 mb-4 border-danger bg-danger-subtle shadow-sm">
                    <h5 class="fw-bold text-danger mb-1"><i class="bi bi-x-circle me-1"></i> Hasil Seleksi Belum Berhasil</h5>
                    <p class="text-dark small mb-0">
                        Terima kasih atas partisipasi dan antusiasme Anda dalam mengikuti proses seleksi. Mohon maaf, untuk posisi ini kualifikasi Anda belum sesuai dengan kriteria yang dibutuhkan saat ini. Jangan berkecil hati dan tetap semangat melamar posisi lainnya.
                    </p>
                </div>
            @endif

            <!-- Visual Recruitment Timeline -->
            <div class="card-custom p-4 p-md-5 mb-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-diagram-2 text-primary me-2"></i> Timeline Tahapan Rekrutmen</h5>
                        <small class="text-muted">Status diperbarui secara real-time langsung dari database seleksi</small>
                    </div>
                    <span class="badge bg-light text-muted border">
                        <i class="bi bi-shield-check text-success me-1"></i> Diverifikasi Tim Rekrutmen
                    </span>
                </div>

                <!-- Timeline Component -->
                <div class="timeline-steps">
                    @php
                    $stageKeys = array_keys($stages);
                    $currentStageIndex = 0;

                    if ($currentStatus === 'Applied') $currentStageIndex = 0;
                    elseif ($currentStatus === 'HR Review') $currentStageIndex = 1;
                    elseif ($currentStatus === 'Document Screening') $currentStageIndex = 2;
                    elseif ($currentStatus === 'Interview Scheduling') $currentStageIndex = 3;
                    elseif ($currentStatus === 'Interview') $currentStageIndex = 4;
                    elseif ($currentStatus === 'Final Decision') $currentStageIndex = 5;
                    elseif (in_array($currentStatus, ['Accepted', 'Rejected'])) $currentStageIndex = 6;

                    $idx = 0;
                    @endphp
                    @foreach ($stages as $key => $info)
                    @php
                        $hData = $historyByStage[$key] ?? null;
                        $isCompleted = ($idx < $currentStageIndex) || ($idx === $currentStageIndex && in_array($currentStatus, ['Accepted']));
                        $isCurrent = ($idx === $currentStageIndex) && !in_array($currentStatus, ['Accepted', 'Rejected']);
                        
                        $stepClass = '';
                        if ($isCompleted) $stepClass = 'completed';
                        elseif ($isCurrent) $stepClass = 'current';
                    
@endphp
                        <div class="timeline-step {{ $stepClass }}">
                            <div class="timeline-icon">
                                <i class="bi {{ $isCompleted ? 'bi-check-lg' : ($isCurrent ? 'bi-hourglass-split' : $info['icon']) }}"></i>
                            </div>
                            <div>
                                <div class="timeline-title">{{ $info['label'] }}</div>
                                <div class="timeline-date">
                                    @if ($hData)
                                        {{ formatTanggalIndo($hData['changed_at']) }}
                                    @elseif ($isCurrent)
                                        <span class="text-primary fw-bold">Sedang Berlangsung</span>
                                    @else
                                        <span class="text-muted">Menunggu</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @php $idx++; @endphp
                    @endforeach

                    <!-- Final Outcome Step -->
                    @if ($currentStatus === 'Accepted')
                        <div class="timeline-step completed">
                            <div class="timeline-icon bg-success border-success text-white">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div>
                                <div class="timeline-title text-success">Accepted</div>
                                <div class="timeline-date text-success fw-bold">Diterima Bekerja</div>
                            </div>
                        </div>
                    @elseif ($currentStatus === 'Rejected')
                        <div class="timeline-step rejected">
                            <div class="timeline-icon bg-danger border-danger text-white">
                                <i class="bi bi-x-lg"></i>
                            </div>
                            <div>
                                <div class="timeline-title text-danger">Rejected</div>
                                <div class="timeline-date text-danger fw-bold">Belum Lolos</div>
                            </div>
                        </div>
                    @else
                        <div class="timeline-step">
                            <div class="timeline-icon">
                                <i class="bi bi-flag"></i>
                            </div>
                            <div>
                                <div class="timeline-title">Hasil Akhir</div>
                                <div class="timeline-date">Keputusan</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scheduled Interview Details (If Available) -->
            @if ($interview)
                <div class="card-custom p-4 mb-4 border-warning shadow-sm bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar2-check text-warning me-2"></i> Detail Jadwal Interview</h5>
                        <span class="badge bg-warning text-dark">{{ $interview['status'] }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="small text-muted d-block">Tanggal Wawancara:</span>
                            <strong class="text-primary"><i class="bi bi-calendar-event me-1"></i> {{ formatTanggalIndo($interview['tanggal']) }}</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="small text-muted d-block">Waktu:</span>
                            <strong><i class="bi bi-clock me-1"></i> {{ substr($interview['waktu'], 0, 5) }} WIB</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="small text-muted d-block">Tipe Sesi:</span>
                            <span class="badge bg-light text-dark border">{{ $interview['type'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="small text-muted d-block">Pewawancara (Interviewer):</span>
                            <strong>{{ $interview['nama_interviewer'] }}</strong>
                            <small class="text-muted d-block">{{ $interview['jabatan_interviewer'] ?? 'HR Team' }}</small>
                        </div>
                        @if (!empty($interview['meeting_link']))
                            <div class="col-12 pt-2">
                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="small text-muted d-block">Tautan Pertemuan Virtual:</span>
                                        <a href="{{ $interview['meeting_link'] }}" target="_blank" class="fw-bold text-primary">
                                            {{ $interview['meeting_link'] }}
                                        </a>
                                    </div>
                                    <a href="{{ $interview['meeting_link'] }}" target="_blank" class="btn btn-success btn-sm fw-bold">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Masuk Ruang Interview
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if (!empty($interview['notes']))
                            <div class="col-12">
                                <span class="small text-muted d-block">Catatan & Panduan Interview dari HR:</span>
                                <div class="p-2.5 bg-light rounded border small text-secondary">
                                    {!! nl2br(e($interview['notes'])) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Complete History Log from Relational Table -->
            <div class="card-custom p-4 border-0 shadow-sm bg-white">
                <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-journal-text text-primary me-2"></i> Log Riwayat Perubahan Status (Database History)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Tahapan (Stage)</th>
                                <th>Status</th>
                                <th>Catatan / Evaluasi HR</th>
                                <th>Diverifikasi Oleh</th>
                                <th>Waktu Pembaruan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $item)
                                <tr>
                                    <td><strong>{{ $item['stage'] }}</strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item['status'] }}</span>
                                    </td>
                                    <td class="text-secondary">{{ $item['notes'] ?? '-' }}</td>
                                    <td>
                                        {{ $item['nama_petugas'] ?? 'Sistem SIREKA' }}
                                    </td>
                                    <td class="text-muted">{{ date('d M Y, H:i', strtotime($item['changed_at'])) }} WIB</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

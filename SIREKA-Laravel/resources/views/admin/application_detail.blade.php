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
                    <h5 class="fw-bold mb-0">Dossier Penilaian Lamaran #APP-{{ str_pad($app['id_application'], 4, '0', STR_PAD_LEFT) }}</h5>
                    <small class="text-muted">{{ $app['nama_kandidat'] }} &bull; {{ $app['nama_job'] }}</small>
                </div>
            </div>
            <a href="{{ route('admin.applications') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Lamaran
            </a>
        @include('partials.topbar_user')
        </header>

        <div class="p-4">
            {!! renderFlash() !!}

            <div class="row g-4">
                <!-- Left Column: Candidate & Job Details -->
                <div class="col-lg-8">
                    <!-- Candidate Dossier Card -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                                    {{ strtoupper(substr($app['nama_kandidat'], 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="fw-bold text-dark mb-0">{{ $app['nama_kandidat'] }}</h4>
                                    <span class="text-muted small">NIK: {{ $app['nik'] }} &bull; {{ $app['pendidikan_terakhir'] }}, {{ $app['institusi'] }} ({{ $app['status_pendidikan'] === 'Lulus' ? 'Lulus' : 'Perkiraan lulus' }} {{ $app['tahun_lulus'] }})</span>
                                </div>
                            </div>
                            <div>
                                <a href="{{ url('/') }}/uploads/cv/{{ $app['cv_file'] }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Unduh / Buka CV
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 small mb-4">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Alamat Email:</span>
                                <strong>{{ $app['email_kandidat'] }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Telepon:</span>
                                <strong>{{ $app['no_telepon'] }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Tanggal Lahir:</span>
                                <strong>{{ formatTanggalIndo($app['tanggal_lahir']) }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Alamat Domisili:</span>
                                <strong>{{ $app['alamat_kandidat'] }}</strong>
                            </div>
                            @if (!empty($app['portfolio_url']))
                                <div class="col-12">
                                    <span class="text-muted d-block">Tautan Portofolio / GitHub / LinkedIn:</span>
                                    <a href="{{ $app['portfolio_url'] }}" target="_blank" class="fw-bold text-primary">
                                        {{ $app['portfolio_url'] }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if (!empty($app['cover_letter']))
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <strong class="small d-block mb-1 text-dark">Surat Pengantar (Cover Letter):</strong>
                                <p class="text-secondary small mb-0" style="white-space: pre-line;">{{ $app['cover_letter'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Match Score & Skills Evaluation Card -->
                    <div class="card-custom p-4 mb-4 border-primary-subtle shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-bullseye text-primary me-2"></i> Evaluasi Skill Match (Kecocokan Posisi)</h5>
                                <small class="text-muted">Formula: ({{ $skillsDetail['matched_count'] }} Cocok / {{ $skillsDetail['total_required'] }} Dibutuhkan) &times; 100%</small>
                            </div>
                            <div class="text-end">
                                <span class="fs-3 fw-extrabold text-primary">{{ $skillsDetail['score'] }}%</span>
                                <span class="badge {{ $skillsDetail['score'] >= 80 ? 'bg-success' : ($skillsDetail['score'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} ms-1">
                                    {{ $skillsDetail['score'] >= 80 ? 'High Match' : ($skillsDetail['score'] >= 50 ? 'Medium Match' : 'Low Match') }}
                                </span>
                            </div>
                        </div>

                        <div class="progress-match mb-3" style="height: 10px;">
                            <div class="progress-bar {{ $skillsDetail['score'] >= 80 ? 'bg-success' : ($skillsDetail['score'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $skillsDetail['score'] }}%"></div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($skillsDetail['skills'] as $sk)
                                <span class="badge {{ $sk['matched'] ? 'bg-success text-white' : 'bg-light text-muted border' }} p-2 d-inline-flex align-items-center gap-1.5">
                                    <i class="bi {{ $sk['matched'] ? 'bi-check-circle-fill' : 'bi-x-circle' }}"></i>
                                    {{ $sk['nama_skill'] }}
                                    <small class="opacity-75">{{ $sk['matched'] ? '(Tersedia)' : '(Belum Ada)' }}</small>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Stage History Timeline Log -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-clock-history text-primary me-2"></i> Riwayat Perkembangan Tahapan Seleksi
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tahapan</th>
                                        <th>Status</th>
                                        <th>Catatan Evaluasi</th>
                                        <th>Petugas HR</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $h)
                                        <tr>
                                            <td><strong>{{ $h['stage'] }}</strong></td>
                                            <td><span class="badge bg-light text-dark border">{{ $h['status'] }}</span></td>
                                            <td class="text-secondary">{{ $h['notes'] ?? '-' }}</td>
                                            <td>{{ $h['nama_petugas'] ?? 'Pelamar' }}</td>
                                            <td class="text-muted">{{ date('d M Y, H:i', strtotime($h['changed_at'])) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Status Controls & Interview Scheduler -->
                <div class="col-lg-4">
                    <!-- Update Status Card -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-sliders text-primary me-2"></i> Perbarui Status Seleksi
                        </h5>
                        <div class="mb-3">
                            <span class="small text-muted d-block mb-1">Status Saat Ini:</span>
                            {!! getStatusBadge($app['current_status']) !!}
                            @if ($poolStatus)
                                <span class="badge bg-purple text-white ms-1"><i class="bi bi-star-fill me-1"></i>Talent Pool ({{ $poolStatus }})</span>
                            @endif
                        </div>
                        <div class="small text-muted mb-3">
                            PIC lowongan: <strong>{{ $app['nama_pic'] ?? 'Belum ada PIC' }}</strong>
                        </div>

                        @if (!$canProcess)
                            <div class="alert alert-secondary small mb-0">
                                <i class="bi bi-lock me-1"></i> Anda hanya bisa melihat lamaran ini. Yang boleh memproses: PIC lowongan atau kepala HR.
                            </div>
                        @else
                        <form method="POST" action="{{ route('admin.application_detail') }}?id={{ $appId }}">
@csrf
                            <input type="hidden" name="action_update_status" value="1">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Status Baru <span class="text-danger">*</span></label>
                                <select name="new_status" id="newStatus" class="form-select" required>
                                    <option value="Applied" {{ $app['current_status'] === 'Applied' ? 'selected' : '' }}>Applied</option>
                                    <option value="HR Review" {{ $app['current_status'] === 'HR Review' ? 'selected' : '' }}>HR Review</option>
                                    <option value="Document Screening" {{ $app['current_status'] === 'Document Screening' ? 'selected' : '' }}>Document Screening</option>
                                    <option value="Interview Scheduling" {{ $app['current_status'] === 'Interview Scheduling' ? 'selected' : '' }}>Interview Scheduling</option>
                                    <option value="Interview" {{ $app['current_status'] === 'Interview' ? 'selected' : '' }}>Interview</option>
                                    <option value="Final Decision" {{ $app['current_status'] === 'Final Decision' ? 'selected' : '' }}>Final Decision</option>
                                    <option value="Accepted" {{ $app['current_status'] === 'Accepted' ? 'selected' : '' }}>Accepted (Terima & Terbitkan LoA)</option>
                                    <option value="Rejected" {{ $app['current_status'] === 'Rejected' ? 'selected' : '' }}>Rejected (Tolak)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Catatan Evaluasi / Alasan</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Tuliskan catatan internal atau alasan keputusan..."></textarea>
                            </div>

                            <!-- Muncul hanya saat status Rejected dipilih -->
                            <div id="talentPoolBox" class="mb-3 p-3 bg-light rounded-3 border d-none">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="add_talent_pool" id="addTalentPool" value="1">
                                    <label class="form-check-label small fw-semibold" for="addTalentPool">Masukkan ke Talent Pool</label>
                                </div>
                                <textarea name="talent_reason" rows="2" class="form-control form-control-sm" placeholder="Alasan kandidat disimpan, cth: kuota penuh, portofolio kuat"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Simpan Status Baru
                            </button>
                        </form>
                        @endif

                        @if ($app['current_status'] === 'Accepted' && !empty($app['loa_id']))
                            <div class="mt-3 pt-3 border-top">
                                <a href="{{ route('user.loa') }}?app_id={{ $appId }}" target="_blank" class="btn btn-success btn-sm w-100 fw-bold">
                                    <i class="bi bi-award me-1"></i> Buka / Cetak Dokumen LoA
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Schedule Interview Card / Trigger -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-camera-video text-warning me-2"></i> Jadwal Interview
                            </h5>
                        </div>

                        @if ($existingInterview)
                            <div class="p-3 bg-light rounded-3 border small mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge bg-warning text-dark">{{ $existingInterview['status'] }}</span>
                                </div>
                                <div class="mb-1"><i class="bi bi-calendar-check me-1 text-primary"></i> {{ formatTanggalIndo($existingInterview['tanggal']) }} ({{ substr($existingInterview['waktu'], 0, 5) }} WIB)</div>
                                <div class="mb-1"><i class="bi bi-person me-1 text-primary"></i> {{ $existingInterview['nama_interviewer'] }}</div>
                                <div class="mb-2"><i class="bi bi-laptop me-1 text-primary"></i> Tipe: {{ $existingInterview['type'] }}</div>
                                @if ($existingInterview['nilai'] !== null)
                                    <div class="mb-2"><i class="bi bi-clipboard-check me-1 text-primary"></i> Nilai: <strong>{{ (int)$existingInterview['nilai'] }}</strong> &bull; Rekomendasi: <strong>{{ $existingInterview['rekomendasi'] }}</strong></div>
                                @endif
                                @if (!empty($existingInterview['meeting_link']))
                                    <a href="{{ $existingInterview['meeting_link'] }}" target="_blank" class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan Meeting
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ($canProcess)
                            @if ($existingInterview)
                                <!-- Input hasil wawancara dari pewawancara -->
                                <form method="POST" action="{{ route('admin.application_detail') }}?id={{ $appId }}" class="row g-2 mb-3">
@csrf
                                    <input type="hidden" name="action_interview_result" value="1">
                                    <input type="hidden" name="id_interview" value="{{ $existingInterview['id_interview'] }}">
                                    <div class="col-5">
                                        <input type="number" name="nilai" min="1" max="100" required class="form-control form-control-sm" placeholder="Nilai 1-100" value="{{ (string)($existingInterview['nilai'] ?? '') }}">
                                    </div>
                                    <div class="col-7">
                                        <select name="rekomendasi" required class="form-select form-select-sm">
                                            <option value="">Rekomendasi...</option>
                                            @foreach (['Lanjut', 'Tidak Lanjut', 'Talent Pool'] as $rek)
                                                <option value="{{ $rek }}" {{ ($existingInterview['rekomendasi'] ?? '') === $rek ? 'selected' : '' }}>{{ $rek }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Simpan Hasil Wawancara</button>
                                    </div>
                                </form>
                            @endif
                            <button class="btn btn-outline-warning text-dark w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#interviewModal">
                                <i class="bi bi-calendar-plus me-1"></i> {{ $existingInterview ? 'Jadwalkan Ulang Interview' : 'Jadwalkan Interview' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Schedule Interview -->
<div class="modal fade" id="interviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.application_detail') }}?id={{ $appId }}">
@csrf
                <input type="hidden" name="action_schedule_interview" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-event me-2"></i> Jadwalkan Sesi Wawancara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Pilih Pewawancara (Interviewer) <span class="text-danger">*</span></label>
                        <select name="interviewer_nik" required class="form-select">
                            <option value="">Pilih Pewawancara...</option>
                            @foreach ($interviewersList as $itw)
                                <option value="{{ $itw['nik'] }}">
                                    {{ $itw['nama'] }} ({{ $itw['jabatan'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Wawancara <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" required class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Waktu / Jam <span class="text-danger">*</span></label>
                        <input type="time" name="waktu" required class="form-control" value="10:00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipe Wawancara</label>
                        <select name="type" class="form-select">
                            <option value="Online">Online (Virtual)</option>
                            <option value="Offline">Offline (Tatap Muka)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Lokasi (Jika Offline)</label>
                        <input type="text" name="location" class="form-control" placeholder="Ruang Meeting Lt. 3">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Tautan Meeting Virtual (Jika Online)</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Catatan / Panduan untuk Kandidat</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Siapkan portofolio dan berpakaian rapi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Konfirmasi Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Kotak "Masukkan ke Talent Pool" hanya ditampilkan saat status Rejected dipilih
    const statusSelect = document.getElementById('newStatus');
    if (statusSelect) {
        const talentBox = document.getElementById('talentPoolBox');
        function toggleTalentBox() {
            talentBox.classList.toggle('d-none', statusSelect.value !== 'Rejected');
        }
        statusSelect.addEventListener('change', toggleTalentBox);
        toggleTalentBox();
    }
</script>
@endsection

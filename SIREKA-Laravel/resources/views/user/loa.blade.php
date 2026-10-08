@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Action Controls (No Print) -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <a href="{{ $isHr ? route('admin.loa_manage') : route('user.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ $isHr ? 'Kembali ke Daftar LoA' : 'Kembali ke Dashboard' }}
        </a>
        <div class="d-flex gap-2 align-items-center">
            @if ($loa)
                <!-- Status jawaban pelamar atas offering -->
                <span class="badge {{ $loa['status'] === 'Accepted' ? 'bg-success' : ($loa['status'] === 'Declined' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $loa['status'] === 'Issued' ? 'Menunggu Jawaban' : ($loa['status'] === 'Accepted' ? 'Diterima Pelamar' : 'Ditolak Pelamar') }}
                </span>
                @if (!$isHr && $loa['status'] === 'Issued')
                    <form method="POST" class="d-flex gap-2" onsubmit="return confirm('Yakin dengan jawaban Anda? Jawaban tidak bisa diubah.');">
@csrf
                        <input type="hidden" name="action_respond_loa" value="1">
                        <input type="hidden" name="id_loa" value="{{ $loa['id_loa'] }}">
                        <button type="submit" name="jawaban" value="Accepted" class="btn btn-success fw-bold">Terima Offering</button>
                        <button type="submit" name="jawaban" value="Declined" class="btn btn-outline-danger">Tolak</button>
                    </form>
                @endif
                <button onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
                </button>
            @endif
        </div>
    </div>

    {!! renderFlash() !!}

    @if (!$loa)
        <div class="card-custom p-5 text-center border-0 shadow-sm">
            <div class="display-4 text-muted mb-2"><i class="bi bi-file-earmark-x"></i></div>
            <h4 class="fw-bold">Belum Ada Letter of Acceptance yang Diterbitkan</h4>
            <p class="text-muted small">Surat penerimaan kerja resmi hanya diterbitkan untuk pelamar yang telah dinyatakan <strong>Accepted</strong> oleh pihak HR perusahaan.</p>
            <a href="{{ route('user.applications') }}" class="btn btn-primary btn-sm">
                Lihat Status Lamaran Saya
            </a>
        </div>
    @else
        <!-- Formal LoA Document Container -->
        <div class="loa-container shadow">
            <!-- Company Letterhead (Kop Surat) -->
            <div class="loa-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark mb-1 text-uppercase">{{ $loa['nama_company'] }}</h3>
                    <div class="text-secondary small">
                        {{ $loa['alamat_company'] }}<br>
                        Email: {{ $loa['email_corporate'] }} | Telp: {{ $loa['telp_company'] }}
                    </div>
                </div>
                <div class="text-end">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 px-3 fw-bold">
                        <i class="bi bi-briefcase-fill fs-4 me-2"></i> SIREKA
                    </div>
                </div>
            </div>

            <!-- Title & Ref Number -->
            <div class="text-center my-4">
                <h4 class="fw-bold text-dark mb-1 text-decoration-underline text-uppercase">LETTER OF ACCEPTANCE</h4>
                <div class="text-muted small">Nomor Surat: <strong>{{ $loa['loa_number'] }}</strong></div>
            </div>

            <!-- Letter Body -->
            <div class="mb-4 small" style="line-height: 1.8;">
                <p>Kepada Yth.,<br>
                <strong>{{ $loa['nama_kandidat'] }}</strong><br>
                NIK: {{ $loa['nik'] }}<br>
                Alamat: {{ $loa['alamat_kandidat'] }}</p>

                <p>Dengan hormat,</p>

                <p>
                    Sehubungan dengan seluruh rangkaian tahapan seleksi rekrutmen dan wawancara yang telah Anda ikuti melalui platform <strong>SIREKA (Sistem Informasi Rekrutmen & Kandidat)</strong>, manajemen <strong>{{ $loa['nama_company'] }}</strong> dengan bangga memberitahukan bahwa Anda dinyatakan:
                </p>

                <div class="text-center my-3 p-3 bg-light border rounded">
                    <span class="fs-5 fw-bold text-success text-uppercase letter-spacing-1">DITERIMA BEKERJA (ACCEPTED)</span>
                </div>

                <p>Adapun rincian penempatan kerja Anda adalah sebagai berikut:</p>

                <table class="table table-sm table-borderless my-2" style="max-width: 600px;">
                    <tr>
                        <td style="width: 200px;"><strong>Posisi Pekerjaan</strong></td>
                        <td>: {{ $loa['position'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Divisi Penempatan</strong></td>
                        <td>: {{ $loa['division'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipe Pekerjaan</strong></td>
                        <td>: {{ $loa['job_type'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sistem Kerja</strong></td>
                        <td>: {{ $loa['sistem_kerja'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Mulai Bekerja</strong></td>
                        <td>: <strong>{{ formatTanggalIndo($loa['join_date']) }}</strong></td>
                    </tr>
                </table>

                <p>
                    {{ $loa['notes'] ?? 'Harap hadir di kantor operasional pada tanggal mulai bekerja pukul 08:30 WIB dengan membawa kelengkapan dokumen asli ijazah dan identitas diri untuk proses orientasi karyawan baru.' }}
                </p>

                <p>Demikian surat penerimaan kerja ini diterbitkan secara resmi melalui sistem untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>

            <!-- Signatures -->
            <div class="loa-signature d-flex justify-content-between align-items-end pt-4">
                <div class="text-center" style="width: 220px;">
                    <div class="small text-muted mb-5">Diterima & Disetujui Oleh:</div>
                    <div class="fw-bold text-dark border-bottom pb-1">{{ $loa['nama_kandidat'] }}</div>
                    <div class="small text-muted">Kandidat Karyawan</div>
                </div>

                <div class="text-center" style="width: 280px;">
                    <div class="small text-muted mb-1">Jakarta, {{ formatTanggalIndo($loa['issue_date']) }}</div>
                    <div class="small text-muted mb-4">{{ $loa['nama_company'] }}</div>
                    <div class="badge bg-success-subtle text-success border border-success mb-2 px-2 py-1 small">
                        <i class="bi bi-patch-check-fill me-1"></i> VERIFIED DIGITAL LoA
                    </div>
                    <div class="fw-bold text-dark border-bottom pb-1">{{ $loa['authorized_by'] }}</div>
                    <div class="small text-muted">Authorized HR Executive</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

# SIREKA (Sistem Informasi Rekrutmen & Kandidat)
> **Official Tech Career & Recruitment ATS Portal - PT Solusi Teknologi Nusantara**  
> *Sistem Informasi Rekrutmen Digital Berbasis Database Relasional Terintegrasi dengan Smart Match Score, Timeline Tracking, dan Talent Pool.*  
> **Mendukung SDGs 8: Decent Work and Economic Growth (Pekerjaan Layak dan Pertumbuhan Ekonomi)**

---

## 🌟 Value Proposition & Konsep Utama

Berbeda dari sekadar website lowongan kerja umum, **SIREKA** dibangun dengan fokus pada efisiensi seleksi, keadilan akses kerja, dan kesinambungan database kandidat:

```
                    SIREKA
                      │
           ┌──────────┼──────────┐
           ↓          ↓          ↓
     MATCHING      TRACKING   TALENT POOL
       SCORE        STATUS      KANDIDAT
           │          │          │
           ↓          ↓          ↓
      "Cocoknya    "Sudah      "Belum lolos
       berapa?"     sampai      bukan berarti
                    mana?"      hilang"
```

1. **Smart Matching Score Engine**: Algoritma relasional membandingkan keahlian pelamar (`user_skill`) dengan kebutuhan posisi (`job_skill`) secara instan.
2. **Relational Tracking Timeline**: Setiap pergerakan seleksi dicatat ke tabel `candidate_stage_history` sehingga pelamar memantau tahapan seleksi secara transparan.
3. **Talent Pool System**: Kandidat berprestasi yang belum lolos karena keterbatasan kuota disimpan di `talent_pool` untuk diprioritaskan saat ada pembukaan lowongan berikutnya.
4. **Digital Letter of Acceptance (LoA)**: Kandidat yang diterima langsung diterbitkan surat penerimaan resmi berstempel dan bernomor unik yang dapat dicetak langsung ke PDF melalui browser.

---

## 🛠️ Spesifikasi Teknologi

- **Bahasa Pemrograman**: PHP Native (PDO + Prepared Statements)
- **Database**: MySQL (InnoDB, Foreign Key Constraints, Normalized)
- **Web Server & Environment**: Laragon (Apache + MySQL)
- **Frontend UI**: HTML5, CSS3, JavaScript, Bootstrap 5, Bootstrap Icons, Chart.js
- **Keamanan**: Password Hashing (`password_hash()`, `password_verify()`), Session Authentication, Input Sanitization, Role-Based Access Control (RBAC), File Upload Validation.

---

## 📁 Struktur Folder Project

```
Project Basis Data/
│
├── config/
│   └── database.php                # Koneksi PDO, Helper Match Score, Session & Autentikasi
│
├── auth/
│   ├── login.php                   # Halaman Login (Auto-deteksi Role)
│   ├── register.php                # Pendaftaran Akun Pelamar (Validasi NIK 16 digit)
│   ├── process_login.php           # Pemrosesan Login & Verifikasi Bcrypt Hash
│   └── logout.php                  # Penghancuran Sesi & Redirect
│
├── user/                           # Portal Pelamar (Applicant Space)
│   ├── dashboard.php               # Ringkasan status lamaran & jadwal interview
│   ├── jobs.php                    # Eksplorasi lowongan dengan live Match Score
│   ├── apply.php                   # Form lamaran, unggah CV & konfirmasi
│   ├── applications.php            # Daftar seluruh lamaran yang diajukan
│   ├── tracking.php                # Visual Timeline Tracking Tahapan Seleksi
│   ├── interview.php               # Jadwal wawancara & tautan virtual meeting
│   ├── profile.php                 # Kelola biodata & katalog keahlian (USER_SKILL)
│   ├── loa.php                     # Dokumen resmi Letter of Acceptance (Printable)
│   ├── talent_pool.php             # Notifikasi & status di dalam Talent Pool
│   ├── complaint.php               # Layanan tiket keluhan / pertanyaan
│   └── feedback.php                # Form rating bintang & ulasan kepuasan
│
├── admin/                          # Portal HR & Administrator
│   ├── dashboard.php               # Statistik eksekutif & visualisasi Chart.js
│   ├── companies.php               # CRUD Data Perusahaan Mitra
│   ├── divisions.php               # CRUD Divisi per Perusahaan
│   ├── jobs.php                    # CRUD Lowongan Kerja & Mapping JOB_SKILL
│   ├── candidates.php              # Direktori seluruh pelamar & ringkasan skill
│   ├── applications.php            # Manajemen seluruh lamaran masuk & filter
│   ├── application_detail.php      # Dossier pelamar, update status & jadwal interview
│   ├── interviews.php              # Penjadwalan & manajemen sesi wawancara
│   ├── talent_pool.php             # Database kandidat Talent Pool & filter keahlian
│   ├── loa_manage.php              # Pengelolaan & penerbitan LoA
│   ├── complaints.php              # Penanganan & respon tiket keluhan
│   ├── feedback.php                # Rekap penilaian kepuasan pelamar
│   ├── reports.php                 # Laporan seleksi per lowongan & divisi (GROUP BY)
│   └── settings.php                # Pengaturan WhatsApp Helpdesk & pejabat LoA
│
├── includes/
│   ├── header.php                  # Global HTML head & stylesheet
│   ├── navbar.php                  # Navigasi utama publik
│   ├── sidebar_admin.php           # Navigasi sidebar admin
│   ├── sidebar_user.php            # Navigasi sidebar pelamar
│   └── footer.php                  # Global footer & scripts
│
├── assets/
│   ├── css/style.css               # Corporate Blue Design System & Timeline styles
│   └── js/main.js                  # Client-side interactive scripts
│
├── uploads/
│   ├── cv/                         # Penyimpanan berkas CV (PDF/DOC/DOCX)
│   └── profiles/                   # Foto profil
│
├── index.php                       # Landing Page Karir IT resmi perusahaan
├── about.php                       # Profil Perusahaan IT, Engineering Values, & Divisi
├── jobs.php                        # Pencarian lowongan IT dengan filter multi-kriteria
├── job_detail.php                  # Rincian lowongan & breakdown kecocokan skill
├── how_it_works.php                # Penjelasan konsep sistem & relevansi SDGs 8
├── helpdesk.php                    # Pusat bantuan & tombol langsung WhatsApp
├── database.sql                    # Skema database relasional & data demo lengkap
└── README.md                       # Dokumentasi instalasi dan panduan pengujian
```

---

## 🚀 Panduan Menjalankan Project di Laragon

### 1. Persiapan Folder
Pastikan folder project ini berada di dalam direktori `www` Laragon, misalnya:
`C:\laragon\www\sireka` atau `C:\laragon\www\Project Basis Data`.

### 2. Jalankan Laragon
1. Buka aplikasi **Laragon**.
2. Klik tombol **"Start All"** untuk menjalankan service **Apache** dan **MySQL**.

### 3. Import Database ke MySQL
Terdapat dua cara mudah untuk mengimport database:

#### Cara A: Melalui HeidiSQL (Bawaan Laragon)
1. Pada Laragon, klik tombol **"Database"** (HeidiSQL akan terbuka otomatis).
2. Klik **"Open"** untuk terhubung ke MySQL localhost (`root`, tanpa password).
3. Buat database baru bernama `sireka_db` (atau biarkan script membuatnya otomatis).
4. Klik menu **File** -> **Load SQL file...**
5. Pilih file `database.sql` yang berada di folder project ini.
6. Tekan tombol **Execute / Run (F9)** untuk menjalankan seluruh query pembuatan tabel dan seed data.

#### Cara B: Melalui phpMyAdmin
1. Buka browser dan akses: `http://localhost/phpmyadmin`
2. Klik tab **Import**.
3. Pilih file `database.sql`.
4. Klik tombol **Import / Kirim** di bagian bawah.

### 4. Konfigurasi Database (Jika Diperlukan)
File konfigurasi terletak di `config/database.php`. Secara default telah tersetel untuk standar Laragon:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sireka_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
```
*Jika MySQL Anda menggunakan password, sesuaikan nilai `DB_PASS` pada file tersebut.*

### 5. Akses Website melalui Browser
Buka browser favorit Anda dan akses URL:
- Jika folder bernama `sireka`:  
  👉 **`http://localhost/sireka`** atau **`http://sireka.test`**
- Jika folder bernama `Project Basis Data`:  
  👉 **`http://localhost/Project%20Basis%20Data`**

---

## 🔑 Akun Default untuk Demo Pengujian

Pada halaman login (`/auth/login.php`), telah disediakan tombol **"Klik untuk Mengisi"** otomatis untuk memudahkan presentasi.

| Role | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin / HR** | `admin@sireka.com` | `admin123` | Akses penuh ke seluruh panel manajemen rekrutmen perusahaan |
| **Interviewer** | `dewi.recruiter@sireka.com` | `admin123` | Tim psikologi & pewawancara teknis |
| **Pelamar 1 (Diterima & Ada LoA)** | `budi.santoso@gmail.com` | `user123` | Lolos Data Analyst (Match Score 100%), memiliki LoA resmi |
| **Pelamar 2 (Tahap Interview)** | `siti.rahmawati@gmail.com` | `user123` | Terjadwal wawancara online via Google Meet |
| **Pelamar 3 (Tahap Screening)** | `rizky.fadillah@gmail.com` | `user123` | Lamaran posisi Senior Backend Engineer |
| **Pelamar 4 (Tahap Talent Pool)** | `andi.pratama@gmail.com` | `user123` | Kandidat potensial tersimpan di Talent Pool |
| **Pelamar 5 (Tahap Rejected)** | `kevin.sanjaya@gmail.com` | `user123` | Contoh hasil evaluasi belum memenuhi syarat teknis |

*Anda juga dapat mendaftarkan akun baru kapan saja melalui menu **Register**.*

---

## 🎯 Skenario Demo untuk Presentasi Kuliah

### Skenario 1: Alur Pelamar (Applicant Workflow)
1. **Pendaftaran Akun**: Buka menu **Register**, daftarkan akun baru dengan NIK (16 digit angka).
2. **Katalog Keahlian**: Masuk ke **Profil & Keahlian**, tambahkan keahlian seperti `Python`, `SQL`, atau `Excel`.
3. **Pencarian Lowongan**: Buka menu **Lowongan**, gunakan filter Tipe Pekerjaan (Kerja/Magang/MT), Perusahaan, dan Divisi. Perhatikan badge persentase **Match Score** yang otomatis terhitung.
4. **Melamar Posisi**: Buka detail posisi, lihat komparasi skill, isi formulir lamaran dan unggah berkas CV.
5. **Tracking Timeline**: Setelah submit, buka menu **Tracking** untuk melihat visual tahapan yang langsung tercatat di tabel `candidate_stage_history`.
6. **Melihat LoA**: Login sebagai `budi.santoso@gmail.com`, buka menu **Letter of Acceptance**, lalu klik tombol **Cetak / Simpan PDF** untuk melihat surat penerimaan resmi.

### Skenario 2: Alur Admin / HR (Company Workflow)
1. **Login Admin**: Masuk menggunakan akun `admin@sireka.com` / `admin123`.
2. **Dashboard Eksekutif**: Tinjau statistik lowongan, pelamar, dan dua grafik Chart.js (Distribusi Status & Lamaran per Divisi).
3. **Manajemen Lowongan**: Buka menu **Lowongan Kerja**, buka form baru, dan tentukan keahlian yang dipersyaratkan via checklist (mengisi tabel relasi `job_skill`).
4. **Review Dossier Pelamar**: Buka menu **Lamaran Masuk**, pilih salah satu pelamar, tinjau Match Score, dan periksa berkas CV.
5. **Update Tahapan Seleksi**: Ubah status menjadi `Interview Scheduling` atau `Interview`, masukkan catatan HR, dan klik Simpan. Amati bagaimana riwayat langsung bertambah di timeline.
6. **Jadwalkan Wawancara**: Tentukan pewawancara, tanggal, jam, dan link Google Meet.
7. **Penerimaan & LoA Otomatis**: Ubah status kandidat menjadi `Accepted`. Sistem secara otomatis meng-generate nomor surat LoA resmi pada tabel `loa`.
8. **Pengelolaan Talent Pool**: Pindahkan kandidat potensial ke `Talent Pool`. Buka menu **Talent Pool** untuk melihat data tersimpan dan memfilternya berdasarkan skill.
9. **Laporan Relasional**: Buka menu **Laporan & Statistik** untuk melihat hasil query agregasi `GROUP BY` per lowongan dan divisi.

---

## 🗄️ Relasi Database (Konsep Basis Data)

Sistem ini menerapkan prinsip normalisasi basis data dengan integritas referensial:
- **One-to-Many**:
  - `company` &rarr; `division` (1 Perusahaan memiliki banyak Divisi)
  - `division` &rarr; `job` (1 Divisi memiliki banyak Lowongan)
  - `company` &rarr; `interviewer` (1 Perusahaan memiliki banyak Pewawancara)
  - `job` &rarr; `application` (1 Lowongan memiliki banyak Lamaran)
  - `user_all` &rarr; `application` (1 Pelamar memiliki banyak Lamaran)
  - `application` &rarr; `candidate_stage_history` (1 Lamaran memiliki banyak Riwayat Status)
  - `application` &rarr; `interview` (1 Lamaran dapat memiliki sesi Wawancara)
- **One-to-One**:
  - `application` &rarr; `loa` (1 Lamaran yang diterima memiliki 1 Surat Penerimaan resmi unik)
- **Many-to-Many (Junction Table)**:
  - `user_all` &harr; `skill` melalui tabel `user_skill` (Composite PK: `nik`, `id_skill`)
  - `job` &harr; `skill` melalui tabel `job_skill` (Composite PK: `id_job`, `id_skill`)
  - `company` &harr; `user_all` melalui tabel `company_admin`

---

## 📞 Layanan Bantuan (Helpdesk)
Untuk pertanyaan seputar sistem atau bantuan teknis demo:
- **WhatsApp Support**: Terhubung langsung via tombol Helpdesk (`wa.me/6281234567890`)
- **Email**: `recruitment.support@sireka.id`

---
*Dikembangkan dengan penuh dedikasi untuk Project Mata Kuliah Basis Data.*

# SIREKA — versi Laravel

**Sistem Informasi Rekrutmen & Kandidat** – PT Solusi Teknologi Nusantara (Project Basis Data).

Folder ini adalah versi **Laravel 12** dari project SIREKA PHP native. Sistem, alur, tampilan,
dan database **sama persis** dengan versi native; yang berubah hanya cara kodenya disusun
(Route → Middleware → Controller → Blade View).

---

## 1. Kebutuhan (Laragon)

| Kebutuhan | Versi |
|-----------|-------|
| PHP       | **8.2 sampai 8.5** (cek di Laragon: menu *PHP → Version*, atau `php -v` di Terminal Laragon) |
| Composer  | sudah bawaan Laragon |
| MySQL     | bawaan Laragon (user `root`, tanpa password, port 3306) |

Kalau PHP di Laragon masih 8.1 atau lebih lama: unduh PHP 8.2/8.3 (versi *Thread Safe*, x64) dari
windows.php.net, ekstrak ke `C:\laragon\bin\php\`, lalu pilih di menu Laragon *PHP → Version*.

## 2. Cara menjalankan di Laragon

1. Buka Laragon, klik **Start All** (Apache/Nginx + MySQL menyala).
2. (Disarankan) Pindahkan/salin folder `SIREKA-Laravel` ke `C:\laragon\www\`.
3. Klik tombol **Terminal** di Laragon, lalu jalankan:

```bash
cd C:\laragon\www\SIREKA-Laravel

# Install library Laravel (folder vendor/)
composer update

# Buat file .env dari contoh, lalu buat APP_KEY
copy .env.example .env
php artisan key:generate

# Import database sireka_db (menghapus & mengisi ulang sireka_db dengan data demo)
php artisan sireka:import-db
#   atau: buka HeidiSQL / phpMyAdmin dari Laragon, lalu import file database/sireka_db.sql
```

4. Buka aplikasinya, pilih salah satu:
   * **Pretty URL Laragon**: klik kanan Laragon → *Reload* (atau *Apache → Reload*), lalu buka
     **http://sireka-laravel.test**. Laragon otomatis mengarahkan domain ini ke folder `public/`.
     Jika memakai cara ini, ubah `APP_URL=http://sireka-laravel.test` di file `.env`.
   * **Tanpa memindah folder**: jalankan `php artisan serve` di Terminal Laragon lalu buka
     **http://127.0.0.1:8000**.

> Database yang dipakai tetap `sireka_db` dengan struktur dan data yang sama.
> File `database/sireka_db.sql` adalah salinan **identik** dari `database.sql` versi native.
> Session & cache Laravel disimpan di folder `storage/`, jadi **tidak ada tabel tambahan** di database.
> Karena itu, gunakan `php artisan sireka:import-db` (bukan `php artisan migrate`).

Pengaturan koneksi database ada di file `.env`:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sireka_db
DB_USERNAME=root
DB_PASSWORD=
```

## 3. Akun demo (sama dengan versi native)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gmail.com | cobain |
| Kepala HR | admin@sireka.com | admin123 |
| HR | HR@gmail.com / interviewer@gmail.com | cobain |
| HR | dewi.recruiter@sireka.com | admin123 |
| Pelamar | budi.santoso@gmail.com (dan pelamar lain) | user123 |

## 4. Peta file: PHP native → Laravel

| Versi native | Versi Laravel |
|--------------|---------------|
| `config/database.php` (koneksi PDO) | `.env` + `config/database.php` Laravel |
| `config/database.php` (fungsi helper) | `app/helpers.php` (nama fungsi sama: `currentUser()`, `hasRole()`, `calculateMatchScore()`, `getStatusBadge()`, dst.) |
| `requireLogin()` / `requireRole()` | Middleware `app/Http/Middleware/EnsureRole.php` → `->middleware('role:hr')` |
| `includes/header.php` + `footer.php` | `resources/views/layouts/app.blade.php` |
| `includes/navbar.php`, `sidebar_*.php`, `topbar_user.php` | `resources/views/partials/*.blade.php` |
| `includes/about_data.php` | `config/about.php` |
| `index.php`, `about.php`, `jobs.php`, `job_detail.php`, `helpdesk.php` | `PublicController` + `resources/views/public/*` |
| `auth/*.php` | `AuthController` + `resources/views/auth/*` |
| `user/*.php` | `app/Http/Controllers/User/*Controller.php` + `resources/views/user/*` |
| `admin/*.php` | `app/Http/Controllers/Admin/*Controller.php` + `resources/views/admin/*` |
| `assets/` | `public/assets/` |
| `uploads/cv`, `uploads/profiles` | `public/uploads/cv`, `public/uploads/profiles` |
| `database.sql` | `database/sireka_db.sql` |
| `artisan` buatan (serve, migrate:fresh) | `artisan` asli Laravel + perintah `php artisan sireka:import-db` |
| — | `app/Models/*` : model Eloquent untuk ke-15 tabel beserta relasinya (bisa dipakai di `php artisan tinker`) |

Semua route bisa dilihat dengan `php artisan route:list` (file `routes/web.php`).

## 5. Alamat halaman

Alamatnya sama, hanya tanpa akhiran `.php`:

| Native | Laravel |
|--------|---------|
| `/index.php` | `/` |
| `/jobs.php?type=Magang` | `/jobs?type=Magang` |
| `/user/tracking.php?id=3` | `/user/tracking?id=3` |
| `/admin/application_detail.php?id=1` | `/admin/application_detail?id=1` |

Link lama yang masih memakai `.php` otomatis dialihkan ke alamat baru.

## 6. Hal yang sengaja dipertahankan / disesuaikan

* **Query SQL** di setiap halaman sama dengan versi native (dijalankan lewat facade `DB` Laravel dengan
  parameter binding), sehingga hasil Match Score, tracking, talent pool, LoA, dsb. identik.
* Hasil query tetap berupa array (`$row['nama_kolom']`), sama seperti `PDO::FETCH_ASSOC` di versi native.
* Pesan notifikasi (flash), validasi, hak akses (PIC lowongan / kepala HR) dan urutan proses form sama persis.
* Setiap form `POST` sekarang membawa token `@csrf` (fitur keamanan bawaan Laravel).
* Password tetap bcrypt (`$2y$`), jadi semua akun lama tetap bisa login.
* Zona waktu aplikasi `Asia/Jakarta` (bisa diubah lewat `APP_TIMEZONE` di `.env`).

## 7. Deploy (opsional)

* **Railway**: `nixpacks.toml` / `Procfile` sudah disiapkan. Isi variabel `APP_KEY`, `DB_*` (TiDB Cloud) dan
  `MYSQL_ATTR_SSL_CA` di dashboard Railway.
* **Vercel**: `vercel.json` + `api/index.php` (runtime `vercel-php`). Isi variabel environment yang sama.

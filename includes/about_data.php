<?php
/**
 * Data untuk halaman Tentang Kami dan section About Us di landing page.
 *
 * Semua data di file ini masih CONTOH (placeholder).
 * Untuk mengganti: cukup ubah isi array di bawah, tidak perlu menyentuh file lain.
 *
 * Cara mengganti foto:
 * 1. Simpan foto di folder assets/img/team/ (misal: assets/img/team/budi.jpg)
 * 2. Ubah nilai 'foto' menjadi 'assets/img/team/budi.jpg'
 * Link yang diawali http:// atau https:// dianggap gambar dari internet.
 */

// ---------- 1. Anggota tim (5 orang) ----------
$teamMembers = [
    [
        'nama'      => 'Nama Anggota 1',
        'peran'     => 'Project Manager',
        'bio'       => 'Deskripsi singkat anggota 1. Ganti dengan bio asli.',
        'foto'      => 'https://i.pravatar.cc/400?img=12',
        'instagram' => '#',
        'linkedin'  => '#',
        'github'    => '#',
    ],
    [
        'nama'      => 'Nama Anggota 2',
        'peran'     => 'Database Designer',
        'bio'       => 'Deskripsi singkat anggota 2. Ganti dengan bio asli.',
        'foto'      => 'https://i.pravatar.cc/400?img=47',
        'instagram' => '#',
        'linkedin'  => '#',
        'github'    => '#',
    ],
    [
        'nama'      => 'Nama Anggota 3',
        'peran'     => 'Backend Developer',
        'bio'       => 'Deskripsi singkat anggota 3. Ganti dengan bio asli.',
        'foto'      => 'https://i.pravatar.cc/400?img=33',
        'instagram' => '#',
        'linkedin'  => '#',
        'github'    => '#',
    ],
    [
        'nama'      => 'Nama Anggota 4',
        'peran'     => 'Frontend Developer',
        'bio'       => 'Deskripsi singkat anggota 4. Ganti dengan bio asli.',
        'foto'      => 'https://i.pravatar.cc/400?img=45',
        'instagram' => '#',
        'linkedin'  => '#',
        'github'    => '#',
    ],
    [
        'nama'      => 'Nama Anggota 5',
        'peran'     => 'UI/UX Designer',
        'bio'       => 'Deskripsi singkat anggota 5. Ganti dengan bio asli.',
        'foto'      => 'https://i.pravatar.cc/400?img=59',
        'instagram' => '#',
        'linkedin'  => '#',
        'github'    => '#',
    ],
];

// ---------- 2. Galeri foto dinamis (kartu yang melebar saat disorot) ----------
$galleryItems = [
    ['judul' => 'Ruang Kerja',       'keterangan' => 'Suasana kantor yang nyaman untuk berkolaborasi.', 'foto' => 'https://picsum.photos/id/180/900/600'],
    ['judul' => 'Diskusi Tim',       'keterangan' => 'Sesi brainstorming rutin setiap minggu.',         'foto' => 'https://picsum.photos/id/3/900/600'],
    ['judul' => 'Belajar Bersama',   'keterangan' => 'Program pelatihan dan sertifikasi untuk karyawan.', 'foto' => 'https://picsum.photos/id/20/900/600'],
    ['judul' => 'Teknologi Modern',  'keterangan' => 'Perangkat dan tools terbaru untuk setiap engineer.', 'foto' => 'https://picsum.photos/id/0/900/600'],
    ['judul' => 'Acara Perusahaan',  'keterangan' => 'Kegiatan kebersamaan di luar jam kerja.',         'foto' => 'https://picsum.photos/id/1059/900/600'],
];

// ---------- 3. Foto kolase untuk section About Us di landing page (3 foto) ----------
$aboutCollage = [
    'https://picsum.photos/id/180/500/600',
    'https://picsum.photos/id/3/500/400',
    'https://picsum.photos/id/20/500/400',
];

// ---------- 4. Foto untuk section baru di halaman Tentang Kami ----------
// Ganti dengan foto tim kalian, misal 'assets/img/team/foto-tim.jpg'
$aboutPhotos = [
    'intro'   => 'https://picsum.photos/id/1/800/600',     // About Us (di samping judul)
    'mission' => 'https://picsum.photos/id/119/800/600',   // Our Mission
    'story'   => 'https://picsum.photos/id/48/800/600',    // Our Story
    'life'    => 'https://picsum.photos/id/1060/900/700',  // Life at SIREKA (sebelum kontak)
];

/**
 * Mengubah nilai 'foto' menjadi URL yang bisa dipakai di tag <img>.
 * Kalau foto dari internet (http/https), dipakai apa adanya.
 * Kalau foto lokal (misal assets/img/team/budi.jpg), ditambah BASE_URL di depannya.
 */
function aboutImageUrl($path)
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    return BASE_URL . '/' . ltrim($path, '/');
}

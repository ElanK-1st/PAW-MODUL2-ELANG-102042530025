<?php
// hapus.php : konfirmasi + hapus untuk mata_kuliah, materi, dan tugas
// GET  = tampilkan konfirmasi (belum menghapus apa pun)
// POST = hapus, hanya jika token cocok dan ID valid
require 'koneksi.php';
require 'layout.php';

// Daftar putih: nama tabel HANYA dari sini, tidak pernah langsung dari isian pengguna
$izin = [
    'mata_kuliah' => ['nama' => 'mata kuliah', 'label' => "CONCAT(kode, ' - ', nama)", 'kembali' => 'mata_kuliah.php'],
    'materi'      => ['nama' => 'materi',      'label' => 'judul',                      'kembali' => 'materi.php'],
    'tugas'       => ['nama' => 'tugas',       'label' => 'judul',                      'kembali' => 'tugas.php'],
];
$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$tabel = (isset($src['tabel']) && is_string($src['tabel']) && isset($izin[$src['tabel']])) ? $src['tabel'] : null;
if ($tabel === null) { flash('Jenis data tidak dikenal.', 'galat'); pindah('index.php'); }
$cfg = $izin[$tabel];

$id = ambil_id($src['id'] ?? '');
if (!$id) { flash('ID tidak valid.', 'galat'); pindah($cfg['kembali']); }

// Pastikan data ada sebelum ditampilkan / dihapus
$stmt = $conn->prepare("SELECT {$cfg['label']} AS label FROM $tabel WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$baris = $stmt->get_result()->fetch_assoc();
if (!$baris) { flash('Data tidak ditemukan.', 'galat'); pindah($cfg['kembali']); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Token tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        try {
            $del = $conn->prepare("DELETE FROM $tabel WHERE id = ?");
            $del->bind_param('i', $id);
            $del->execute();
            flash(ucfirst($cfg['nama']) . ' berhasil dihapus.');
            pindah($cfg['kembali']);
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() == 1451) { // foreign key RESTRICT: masih dipakai materi/tugas
                $error = 'Data belum dihapus. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
            } else {
                $error = 'Terjadi kesalahan pada database.';
            }
        }
    }
}

mulai('Hapus ' . $cfg['nama']);
?>
<h1><?= e(ucfirst($cfg['nama'])) ?></h1>
<?php if ($error): ?><div class="pesan galat"><?= e($error) ?></div><?php endif; ?>
<p>Hapus <strong><?= e($baris['label']) ?></strong>?</p>
<p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
<form method="post" action="hapus.php">
  <?= csrf_field() ?>
  <input type="hidden" name="tabel" value="<?= e($tabel) ?>">
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <button type="submit" class="tombol bahaya">Ya, hapus data</button>
  <a class="tombol" href="<?= e($cfg['kembali']) ?>">Batal</a>
</form>
<?php selesai();

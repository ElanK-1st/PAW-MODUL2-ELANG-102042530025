<?php
// detail_mata_kuliah.php : detail + daftar materi dan tugas milik mata kuliah
require 'koneksi.php';
require 'layout.php';

$id = ambil_id($_GET['id'] ?? '');
if (!$id) { flash('ID tidak valid.', 'galat'); pindah('mata_kuliah.php'); }

$stmt = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$mk = $stmt->get_result()->fetch_assoc();
if (!$mk) { flash('Mata kuliah tidak ditemukan.', 'galat'); pindah('mata_kuliah.php'); }

$s1 = $conn->prepare('SELECT id, judul FROM materi WHERE mata_kuliah_id = ? ORDER BY judul');
$s1->bind_param('i', $id); $s1->execute();
$materi = $s1->get_result()->fetch_all(MYSQLI_ASSOC);

$s2 = $conn->prepare('SELECT id, judul, status FROM tugas WHERE mata_kuliah_id = ? ORDER BY judul');
$s2->bind_param('i', $id); $s2->execute();
$tugas = $s2->get_result()->fetch_all(MYSQLI_ASSOC);

mulai('Detail mata kuliah');
?>
<h1>Mata kuliah</h1>
<dl>
  <dt>Kode</dt><dd><?= e($mk['kode']) ?></dd>
  <dt>Nama</dt><dd><?= e($mk['nama']) ?></dd>
  <dt>Dosen</dt><dd><?= e($mk['dosen']) ?></dd>
  <dt>Semester</dt><dd><?= (int)$mk['semester'] ?></dd>
</dl>
<h3>Materi</h3>
<?php if (!$materi): ?><div class="kosong">Belum ada materi.</div><?php else: ?>
<ul><?php foreach ($materi as $m): ?>
  <li><a href="detail_materi.php?id=<?= (int)$m['id'] ?>"><?= e($m['judul']) ?></a></li>
<?php endforeach; ?></ul><?php endif; ?>
<h3>Tugas</h3>
<?php if (!$tugas): ?><div class="kosong">Belum ada tugas.</div><?php else: ?>
<ul><?php foreach ($tugas as $t): ?>
  <li><a href="detail_tugas.php?id=<?= (int)$t['id'] ?>"><?= e($t['judul']) ?></a> (<?= e($t['status']) ?>)</li>
<?php endforeach; ?></ul><?php endif; ?>
<p>
  <a class="tombol utama" href="form_mata_kuliah.php?id=<?= (int)$mk['id'] ?>">Edit data</a>
  <a class="tombol" href="mata_kuliah.php">Kembali</a>
</p>
<?php selesai();

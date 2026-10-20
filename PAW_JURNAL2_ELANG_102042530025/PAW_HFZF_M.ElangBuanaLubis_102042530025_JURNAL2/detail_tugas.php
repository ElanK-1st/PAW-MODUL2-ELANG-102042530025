<?php
// detail_tugas.php : detail tugas + nama mata kuliah (JOIN)
require 'koneksi.php';
require 'layout.php';

$id = ambil_id($_GET['id'] ?? '');
if (!$id) { flash('ID tidak valid.', 'galat'); pindah('tugas.php'); }

$stmt = $conn->prepare(
    'SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, k.kode, k.nama
       FROM tugas t JOIN mata_kuliah k ON k.id = t.mata_kuliah_id WHERE t.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
if (!$t) { flash('Tugas tidak ditemukan.', 'galat'); pindah('tugas.php'); }

mulai('Detail tugas');
?>
<h1>Tugas</h1>
<h3><?= e($t['judul']) ?></h3>
<dl>
  <dt>Mata kuliah</dt><dd><?= e($t['kode'] . ' - ' . $t['nama']) ?></dd>
  <dt>Judul tugas</dt><dd><?= e($t['judul']) ?></dd>
  <dt>Instruksi</dt><dd><?= nl2br(e($t['instruksi'])) ?></dd>
  <dt>Tenggat</dt><dd><?= e(date('d/m/Y', strtotime($t['tenggat']))) ?></dd>
  <dt>Status</dt><dd><?= e($t['status']) ?></dd>
</dl>
<p>
  <a class="tombol utama" href="form_tugas.php?id=<?= (int)$t['id'] ?>">Edit data</a>
  <a class="tombol" href="tugas.php">Kembali</a>
</p>
<?php selesai();

<?php
// detail_materi.php : detail materi + nama mata kuliah (JOIN)
require 'koneksi.php';
require 'layout.php';

$id = ambil_id($_GET['id'] ?? '');
if (!$id) { flash('ID tidak valid.', 'galat'); pindah('materi.php'); }

$stmt = $conn->prepare(
    'SELECT m.id, m.judul, m.isi, m.tautan, k.kode, k.nama
       FROM materi m JOIN mata_kuliah k ON k.id = m.mata_kuliah_id WHERE m.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$m = $stmt->get_result()->fetch_assoc();
if (!$m) { flash('Materi tidak ditemukan.', 'galat'); pindah('materi.php'); }

mulai('Detail materi');
?>
<h1>Materi</h1>
<h3><?= e($m['judul']) ?></h3>
<dl>
  <dt>Mata kuliah</dt><dd><?= e($m['kode'] . ' - ' . $m['nama']) ?></dd>
  <dt>Judul materi</dt><dd><?= e($m['judul']) ?></dd>
  <dt>Isi materi</dt><dd><?= nl2br(e($m['isi'])) ?></dd>
  <dt>Tautan referensi</dt>
  <dd><?php if ($m['tautan'] !== '' && preg_match('#^https?://#i', $m['tautan'])): ?>
        <a href="<?= e($m['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($m['tautan']) ?></a>
      <?php else: ?>-<?php endif; ?></dd>
</dl>
<p>
  <a class="tombol utama" href="form_materi.php?id=<?= (int)$m['id'] ?>">Edit data</a>
  <a class="tombol" href="materi.php">Kembali</a>
</p>
<?php selesai();

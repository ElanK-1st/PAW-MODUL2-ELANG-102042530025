<?php
// materi.php : daftar materi + pencarian judul + filter mata kuliah
require 'koneksi.php';
require 'layout.php';

$q  = get_str('q');
$mk = ambil_id(get_str('mk')) ?? 0;          // 0 = semua mata kuliah
$like = '%' . addcslashes($q, '%_\\') . '%';

$stmt = $conn->prepare(
    'SELECT m.id, m.judul, k.kode, k.nama
       FROM materi m JOIN mata_kuliah k ON k.id = m.mata_kuliah_id
      WHERE m.judul LIKE ? AND (? = 0 OR m.mata_kuliah_id = ?)
      ORDER BY m.judul');
$stmt->bind_param('sii', $like, $mk, $mk);
$stmt->execute();
$baris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$daftar_mk = semua_mk($conn);

mulai('Materi');
?>
<h1>Materi</h1>
<div class="bar">
  <span>Menampilkan <?= count($baris) ?> data.</span>
  <a class="tombol utama" href="form_materi.php">Tambah materi</a>
</div>
<form method="get" class="cari">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul materi">
  <select name="mk"><?= opsi_mk($daftar_mk, $mk, 'Semua mata kuliah') ?></select>
  <button type="submit" class="tombol utama">Cari</button>
  <a class="tombol" href="materi.php">Reset</a>
</form>
<?php if (!$baris): ?>
  <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
<table>
  <tr><th>Judul</th><th>Mata kuliah</th><th>Aksi</th></tr>
  <?php foreach ($baris as $b): ?>
  <tr>
    <td><?= e($b['judul']) ?></td>
    <td><?= e($b['kode'] . ' - ' . $b['nama']) ?></td>
    <td>
      <a class="tombol" href="detail_materi.php?id=<?= (int)$b['id'] ?>">Detail</a>
      <a class="tombol" href="form_materi.php?id=<?= (int)$b['id'] ?>">Edit</a>
      <a class="tombol bahaya" href="hapus.php?tabel=materi&amp;id=<?= (int)$b['id'] ?>">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif;
selesai();

<?php
// mata_kuliah.php : daftar + pencarian (kode atau nama)
require 'koneksi.php';
require 'layout.php';

$q = get_str('q');
$like = '%' . addcslashes($q, '%_\\') . '%';
$stmt = $conn->prepare('SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE kode LIKE ? OR nama LIKE ? ORDER BY nama');
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$baris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

mulai('Mata kuliah');
?>
<h1>Mata kuliah</h1>
<div class="bar">
  <span>Menampilkan <?= count($baris) ?> data.</span>
  <a class="tombol utama" href="form_mata_kuliah.php">Tambah mata kuliah</a>
</div>
<form method="get" class="cari">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama">
  <button type="submit" class="tombol utama">Cari</button>
  <a class="tombol" href="mata_kuliah.php">Reset</a>
</form>
<?php if (!$baris): ?>
  <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
<table>
  <tr><th>Mata kuliah</th><th>Dosen / semester</th><th>Aksi</th></tr>
  <?php foreach ($baris as $b): ?>
  <tr>
    <td><?= e($b['kode'] . ' - ' . $b['nama']) ?></td>
    <td><?= e($b['dosen']) ?> / Semester <?= (int)$b['semester'] ?></td>
    <td>
      <a class="tombol" href="detail_mata_kuliah.php?id=<?= (int)$b['id'] ?>">Detail</a>
      <a class="tombol" href="form_mata_kuliah.php?id=<?= (int)$b['id'] ?>">Edit</a>
      <a class="tombol bahaya" href="hapus.php?tabel=mata_kuliah&amp;id=<?= (int)$b['id'] ?>">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif;
selesai();

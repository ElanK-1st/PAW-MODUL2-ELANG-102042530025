<?php
// tugas.php : daftar tugas + pencarian judul + filter mata kuliah
require 'koneksi.php';
require 'layout.php';

$q  = get_str('q');
$mk = ambil_id(get_str('mk')) ?? 0;
$like = '%' . addcslashes($q, '%_\\') . '%';

$stmt = $conn->prepare(
    'SELECT t.id, t.judul, t.tenggat, t.status, k.kode, k.nama
       FROM tugas t JOIN mata_kuliah k ON k.id = t.mata_kuliah_id
      WHERE t.judul LIKE ? AND (? = 0 OR t.mata_kuliah_id = ?)
      ORDER BY t.tenggat, t.judul');
$stmt->bind_param('sii', $like, $mk, $mk);
$stmt->execute();
$baris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$daftar_mk = semua_mk($conn);

mulai('Tugas');
?>
<h1>Tugas</h1>
<div class="bar">
  <span>Menampilkan <?= count($baris) ?> data.</span>
  <a class="tombol utama" href="form_tugas.php">Tambah tugas</a>
</div>
<form method="get" class="cari">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul tugas">
  <select name="mk"><?= opsi_mk($daftar_mk, $mk, 'Semua mata kuliah') ?></select>
  <button type="submit" class="tombol utama">Cari</button>
  <a class="tombol" href="tugas.php">Reset</a>
</form>
<?php if (!$baris): ?>
  <div class="kosong">Data tidak ditemukan.</div>
<?php else: ?>
<table>
  <tr><th>Judul</th><th>Mata kuliah</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr>
  <?php foreach ($baris as $b): ?>
  <tr>
    <td><?= e($b['judul']) ?></td>
    <td><?= e($b['kode'] . ' - ' . $b['nama']) ?></td>
    <td><?= e(date('d/m/Y', strtotime($b['tenggat']))) ?></td>
    <td><?= e($b['status']) ?></td>
    <td>
      <a class="tombol" href="detail_tugas.php?id=<?= (int)$b['id'] ?>">Detail</a>
      <a class="tombol" href="form_tugas.php?id=<?= (int)$b['id'] ?>">Edit</a>
      <a class="tombol bahaya" href="hapus.php?tabel=tugas&amp;id=<?= (int)$b['id'] ?>">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif;
selesai();

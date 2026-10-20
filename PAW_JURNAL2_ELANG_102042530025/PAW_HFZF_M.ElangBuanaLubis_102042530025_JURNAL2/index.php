<?php
// index.php : beranda, ringkasan dihitung dengan COUNT dari database
require 'koneksi.php';
require 'layout.php';

$jml = [];
foreach (['mata_kuliah', 'materi', 'tugas'] as $tabel) { // nama tabel tetap, bukan dari pengguna
    $jml[$tabel] = (int)$conn->query("SELECT COUNT(*) AS n FROM $tabel")->fetch_assoc()['n'];
}
$daftar = semua_mk($conn);

mulai('Beranda');
?>
<h1>Learning Management System</h1>
<p>Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>
<div class="ringkas">
  <div>Mata kuliah<strong><?= $jml['mata_kuliah'] ?></strong></div>
  <div>Materi<strong><?= $jml['materi'] ?></strong></div>
  <div>Tugas<strong><?= $jml['tugas'] ?></strong></div>
</div>
<p><a class="tombol utama" href="mata_kuliah.php">Kelola mata kuliah</a>
   <a class="tombol utama" href="materi.php">Kelola materi</a>
   <a class="tombol utama" href="tugas.php">Kelola tugas</a></p>
<h3>Mata kuliah tersedia</h3>
<?php if (!$daftar): ?>
  <div class="kosong">Belum ada mata kuliah.</div>
<?php else: ?>
  <ul>
  <?php foreach ($daftar as $m): ?>
    <li><a href="detail_mata_kuliah.php?id=<?= (int)$m['id'] ?>"><?= e($m['kode'] . ' - ' . $m['nama']) ?></a></li>
  <?php endforeach; ?>
  </ul>
<?php endif;
selesai();

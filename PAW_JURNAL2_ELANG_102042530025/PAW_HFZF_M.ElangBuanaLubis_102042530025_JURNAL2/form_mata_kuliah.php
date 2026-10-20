<?php
// form_mata_kuliah.php : tambah (tanpa id) dan ubah (dengan id) mata kuliah
require 'koneksi.php';
require 'layout.php';

$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$edit = array_key_exists('id', $src);
$id = null; $lama = null;

if ($edit) {
    $id = ambil_id($src['id']);
    if (!$id) { flash('ID tidak valid.', 'galat'); pindah('mata_kuliah.php'); }
    $stmt = $conn->prepare('SELECT kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $lama = $stmt->get_result()->fetch_assoc();
    if (!$lama) { flash('Mata kuliah tidak ditemukan.', 'galat'); pindah('mata_kuliah.php'); }
}

$d = $lama ?? ['kode' => '', 'nama' => '', 'dosen' => '', 'semester' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = ['kode' => post_str('kode'), 'nama' => post_str('nama'),
          'dosen' => post_str('dosen'), 'semester' => post_str('semester')];

    if (!csrf_ok()) $errors['umum'] = 'Token tidak valid. Muat ulang halaman lalu coba lagi.';

    // Validasi di server (validasi browser tidak bisa dipercaya)
    if (!preg_match('/^[A-Z0-9-]{3,20}\z/', $d['kode']))
        $errors['kode'] = 'Kode 3-20 karakter: huruf kapital, angka, atau tanda -.';
    if (!panjang_ok($d['nama'], 3, 100))  $errors['nama']  = 'Nama harus 3-100 karakter.';
    if (!panjang_ok($d['dosen'], 3, 100)) $errors['dosen'] = 'Dosen harus 3-100 karakter.';
    $sem = filter_var($d['semester'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 14]]);
    if ($sem === false) $errors['semester'] = 'Semester harus bilangan bulat 1-14.';

    // Kode harus unik (abaikan baris yang sedang diedit)
    if (!isset($errors['kode'])) {
        $cek_id = $id ?? 0;
        $c = $conn->prepare('SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?');
        $c->bind_param('si', $d['kode'], $cek_id);
        $c->execute();
        if ($c->get_result()->num_rows > 0) $errors['kode'] = 'Kode mata kuliah sudah digunakan.';
    }

    if (!$errors) {
        if ($edit && $d['kode'] === $lama['kode'] && $d['nama'] === $lama['nama']
            && $d['dosen'] === $lama['dosen'] && $sem === (int)$lama['semester']) {
            flash('Tidak ada perubahan data.', 'info');
            pindah('mata_kuliah.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?');
                $q->bind_param('sssii', $d['kode'], $d['nama'], $d['dosen'], $sem, $id);
            } else {
                $q = $conn->prepare('INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)');
                $q->bind_param('sssi', $d['kode'], $d['nama'], $d['dosen'], $sem);
            }
            $q->execute();
            flash($edit ? 'Mata kuliah berhasil diubah.' : 'Mata kuliah berhasil ditambahkan.');
            pindah('mata_kuliah.php');
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() == 1062) $errors['kode'] = 'Kode mata kuliah sudah digunakan.'; // UNIQUE
            else $errors['umum'] = 'Data belum tersimpan karena kesalahan database.';
        }
    }
}

mulai($edit ? 'Edit mata kuliah' : 'Tambah mata kuliah');
?>
<h1><?= $edit ? 'Edit' : 'Tambah' ?> mata kuliah</h1>
<?php if (isset($errors['umum'])): ?><div class="pesan galat"><?= e($errors['umum']) ?></div><?php endif; ?>
<form method="post" action="form_mata_kuliah.php" novalidate>
  <?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
  <div class="grid">
    <div><label for="kode">Kode mata kuliah</label>
      <input type="text" id="kode" name="kode" value="<?= e($d['kode']) ?>"><?= galat($errors, 'kode') ?></div>
    <div><label for="nama">Nama mata kuliah</label>
      <input type="text" id="nama" name="nama" value="<?= e($d['nama']) ?>"><?= galat($errors, 'nama') ?></div>
    <div><label for="dosen">Dosen pengampu</label>
      <input type="text" id="dosen" name="dosen" value="<?= e($d['dosen']) ?>"><?= galat($errors, 'dosen') ?></div>
    <div><label for="semester">Semester</label>
      <input type="number" id="semester" name="semester" value="<?= e($d['semester']) ?>"><?= galat($errors, 'semester') ?></div>
  </div>
  <button type="submit" class="tombol utama">Simpan data</button>
  <a class="tombol" href="mata_kuliah.php">Batal</a>
</form>
<?php selesai();

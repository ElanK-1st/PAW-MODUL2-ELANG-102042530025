<?php
// form_tugas.php : tambah (tanpa id) dan ubah (dengan id) tugas
require 'koneksi.php';
require 'layout.php';

$STATUS = ['Draf', 'Aktif', 'Selesai']; // satu-satunya nilai status yang diterima
$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$edit = array_key_exists('id', $src);
$id = null; $lama = null;

if ($edit) {
    $id = ambil_id($src['id']);
    if (!$id) { flash('ID tidak valid.', 'galat'); pindah('tugas.php'); }
    $stmt = $conn->prepare('SELECT mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $lama = $stmt->get_result()->fetch_assoc();
    if (!$lama) { flash('Tugas tidak ditemukan.', 'galat'); pindah('tugas.php'); }
}

$d = $lama ?? ['mata_kuliah_id' => '', 'judul' => '', 'instruksi' => '', 'tenggat' => '', 'status' => 'Draf'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = ['mata_kuliah_id' => post_str('mata_kuliah_id'), 'judul' => post_str('judul'),
          'instruksi' => post_str('instruksi'), 'tenggat' => post_str('tenggat'), 'status' => post_str('status')];

    if (!csrf_ok()) $errors['umum'] = 'Token tidak valid. Muat ulang halaman lalu coba lagi.';

    $mk = ambil_id($d['mata_kuliah_id']);
    if (!$mk) {
        $errors['mata_kuliah_id'] = 'Pilih mata kuliah.';
    } else {
        $c = $conn->prepare('SELECT id FROM mata_kuliah WHERE id = ?');
        $c->bind_param('i', $mk);
        $c->execute();
        if ($c->get_result()->num_rows === 0) $errors['mata_kuliah_id'] = 'Mata kuliah tidak ditemukan.';
    }
    if (!panjang_ok($d['judul'], 3, 150))     $errors['judul']     = 'Judul harus 3-150 karakter.';
    if (!panjang_ok($d['instruksi'], 3, 2000)) $errors['instruksi'] = 'Instruksi harus 3-2000 karakter.';
    if (!tanggal_valid($d['tenggat']))        $errors['tenggat']   = 'Tanggal tenggat tidak valid.';
    if (!in_array($d['status'], $STATUS, true)) $errors['status']  = 'Status harus Draf, Aktif, atau Selesai.';

    if (!$errors) {
        if ($edit && $mk === (int)$lama['mata_kuliah_id'] && $d['judul'] === $lama['judul']
            && $d['instruksi'] === $lama['instruksi'] && $d['tenggat'] === $lama['tenggat']
            && $d['status'] === $lama['status']) {
            flash('Tidak ada perubahan data.', 'info');
            pindah('tugas.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?');
                $q->bind_param('issssi', $mk, $d['judul'], $d['instruksi'], $d['tenggat'], $d['status'], $id);
            } else {
                $q = $conn->prepare('INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)');
                $q->bind_param('issss', $mk, $d['judul'], $d['instruksi'], $d['tenggat'], $d['status']);
            }
            $q->execute();
            flash($edit ? 'Tugas berhasil diubah.' : 'Tugas berhasil ditambahkan.');
            pindah('tugas.php');
        } catch (mysqli_sql_exception $ex) {
            $errors['umum'] = 'Data belum tersimpan karena kesalahan database.';
        }
    }
}

$daftar_mk = semua_mk($conn);
mulai($edit ? 'Edit tugas' : 'Tambah tugas');
?>
<h1><?= $edit ? 'Edit' : 'Tambah' ?> tugas</h1>
<?php if (isset($errors['umum'])): ?><div class="pesan galat"><?= e($errors['umum']) ?></div><?php endif; ?>
<form method="post" action="form_tugas.php" novalidate>
  <?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
  <div class="grid">
    <div><label for="mk">Mata kuliah</label>
      <select id="mk" name="mata_kuliah_id"><?= opsi_mk($daftar_mk, $d['mata_kuliah_id'], 'Pilih Mata kuliah') ?></select>
      <?= galat($errors, 'mata_kuliah_id') ?></div>
    <div><label for="judul">Judul tugas</label>
      <input type="text" id="judul" name="judul" value="<?= e($d['judul']) ?>"><?= galat($errors, 'judul') ?></div>
    <div class="penuh"><label for="instruksi">Instruksi tugas</label>
      <textarea id="instruksi" name="instruksi" rows="5"><?= e($d['instruksi']) ?></textarea><?= galat($errors, 'instruksi') ?></div>
    <div><label for="tenggat">Tenggat</label>
      <input type="date" id="tenggat" name="tenggat" value="<?= e($d['tenggat']) ?>"><?= galat($errors, 'tenggat') ?></div>
    <div><label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach ($STATUS as $s): ?>
          <option value="<?= e($s) ?>"<?= $d['status'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select><?= galat($errors, 'status') ?></div>
  </div>
  <button type="submit" class="tombol utama">Simpan data</button>
  <a class="tombol" href="tugas.php">Batal</a>
</form>
<?php selesai();

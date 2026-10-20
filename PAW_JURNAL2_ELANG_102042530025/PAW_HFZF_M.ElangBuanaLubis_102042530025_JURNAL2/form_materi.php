<?php
// form_materi.php : tambah (tanpa id) dan ubah (dengan id) materi
require 'koneksi.php';
require 'layout.php';

$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$edit = array_key_exists('id', $src);
$id = null; $lama = null;

if ($edit) {
    $id = ambil_id($src['id']);
    if (!$id) { flash('ID tidak valid.', 'galat'); pindah('materi.php'); }
    $stmt = $conn->prepare('SELECT mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $lama = $stmt->get_result()->fetch_assoc();
    if (!$lama) { flash('Materi tidak ditemukan.', 'galat'); pindah('materi.php'); }
}

$d = $lama ?? ['mata_kuliah_id' => '', 'judul' => '', 'isi' => '', 'tautan' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = ['mata_kuliah_id' => post_str('mata_kuliah_id'), 'judul' => post_str('judul'),
          'isi' => post_str('isi'), 'tautan' => post_str('tautan')];

    if (!csrf_ok()) $errors['umum'] = 'Token tidak valid. Muat ulang halaman lalu coba lagi.';

    // Mata kuliah harus angka valid DAN benar-benar ada di database
    $mk = ambil_id($d['mata_kuliah_id']);
    if (!$mk) {
        $errors['mata_kuliah_id'] = 'Pilih mata kuliah.';
    } else {
        $c = $conn->prepare('SELECT id FROM mata_kuliah WHERE id = ?');
        $c->bind_param('i', $mk);
        $c->execute();
        if ($c->get_result()->num_rows === 0) $errors['mata_kuliah_id'] = 'Mata kuliah tidak ditemukan.';
    }
    if (!panjang_ok($d['judul'], 3, 150)) $errors['judul'] = 'Judul harus 3-150 karakter.';
    if (!panjang_ok($d['isi'], 3, 2000))  $errors['isi']   = 'Isi harus 3-2000 karakter.';
    // Tautan opsional, tetapi jika diisi wajib http:// atau https:// (menolak javascript:)
    if ($d['tautan'] !== '' && (mb_strlen($d['tautan']) > 255
        || !preg_match('#^https?://#i', $d['tautan'])
        || !filter_var($d['tautan'], FILTER_VALIDATE_URL)))
        $errors['tautan'] = 'Gunakan tautan http/https yang valid (maksimal 255 karakter).';

    if (!$errors) {
        if ($edit && $mk === (int)$lama['mata_kuliah_id'] && $d['judul'] === $lama['judul']
            && $d['isi'] === $lama['isi'] && $d['tautan'] === $lama['tautan']) {
            flash('Tidak ada perubahan data.', 'info');
            pindah('materi.php');
        }
        try {
            if ($edit) {
                $q = $conn->prepare('UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?');
                $q->bind_param('isssi', $mk, $d['judul'], $d['isi'], $d['tautan'], $id);
            } else {
                $q = $conn->prepare('INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)');
                $q->bind_param('isss', $mk, $d['judul'], $d['isi'], $d['tautan']);
            }
            $q->execute();
            flash($edit ? 'Materi berhasil diubah.' : 'Materi berhasil ditambahkan.');
            pindah('materi.php');
        } catch (mysqli_sql_exception $ex) {
            $errors['umum'] = 'Data belum tersimpan karena kesalahan database.';
        }
    }
}

$daftar_mk = semua_mk($conn);
mulai($edit ? 'Edit materi' : 'Tambah materi');
?>
<h1><?= $edit ? 'Edit' : 'Tambah' ?> materi</h1>
<?php if (isset($errors['umum'])): ?><div class="pesan galat"><?= e($errors['umum']) ?></div><?php endif; ?>
<form method="post" action="form_materi.php" novalidate>
  <?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
  <div class="grid">
    <div><label for="mk">Mata kuliah</label>
      <select id="mk" name="mata_kuliah_id"><?= opsi_mk($daftar_mk, $d['mata_kuliah_id'], 'Pilih Mata kuliah') ?></select>
      <?= galat($errors, 'mata_kuliah_id') ?></div>
    <div><label for="judul">Judul materi</label>
      <input type="text" id="judul" name="judul" value="<?= e($d['judul']) ?>"><?= galat($errors, 'judul') ?></div>
    <div class="penuh"><label for="isi">Isi materi</label>
      <textarea id="isi" name="isi" rows="5"><?= e($d['isi']) ?></textarea><?= galat($errors, 'isi') ?></div>
    <div class="penuh"><label for="tautan">Tautan referensi</label>
      <input type="text" id="tautan" name="tautan" value="<?= e($d['tautan']) ?>"><?= galat($errors, 'tautan') ?></div>
  </div>
  <button type="submit" class="tombol utama">Simpan data</button>
  <a class="tombol" href="materi.php">Batal</a>
</form>
<?php selesai();

<?php
// layout.php : kerangka HTML bersama
function mulai($judul) {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($judul) . ' - LMS</title><link rel="stylesheet" href="style.css"></head><body>'
       . '<header><nav><a href="index.php">Beranda</a><a href="mata_kuliah.php">Mata kuliah</a>'
       . '<a href="materi.php">Materi</a><a href="tugas.php">Tugas</a></nav></header><main>';
    if ($flash) echo '<div class="pesan ' . e($flash['tipe']) . '">' . e($flash['teks']) . '</div>';
}
function selesai() { echo '</main></body></html>'; }

function galat($errors, $k) {
    return isset($errors[$k]) ? '<div class="err">' . e($errors[$k]) . '</div>' : '';
}

function opsi_mk($daftar, $dipilih, $kosong) {
    $h = '<option value="">' . e($kosong) . '</option>';
    foreach ($daftar as $m) {
        $s = ((int)$dipilih === (int)$m['id']) ? ' selected' : '';
        $h .= '<option value="' . (int)$m['id'] . '"' . $s . '>' . e($m['kode'] . ' - ' . $m['nama']) . '</option>';
    }
    return $h;
}

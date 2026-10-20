<?php
// koneksi.php : koneksi database, session, dan fungsi bantu
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // error SQL jadi exception agar bisa ditangkap

try {
    $conn = new mysqli('localhost', 'root', 'El@ng_228', 'lms_paw'); // sesuaikan host, user, password, nama database
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL menyala dan pengaturan di koneksi.php benar.');
}

// Escape teks sebelum dicetak ke HTML (mencegah XSS)
function e($teks) { return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8'); }

function pindah($url) { header('Location: ' . $url); exit; }

// Pesan satu kali tampil: tipe = ok | galat | info
function flash($teks, $tipe = 'ok') { $_SESSION['flash'] = ['teks' => $teks, 'tipe' => $tipe]; }

// Ambil ID bilangan bulat positif; selain itu null (ID "abc", "-1", "1 OR 1=1" ditolak)
function ambil_id($nilai) {
    if (!is_scalar($nilai)) return null;
    $id = filter_var(trim((string)$nilai), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function get_str($k)  { return (isset($_GET[$k])  && is_string($_GET[$k]))  ? trim($_GET[$k])  : ''; }
function post_str($k) { return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : ''; }

function panjang_ok($teks, $min, $max) {
    $n = mb_strlen($teks, 'UTF-8');
    return $n >= $min && $n <= $max;
}

function tanggal_valid($s) {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s && $s >= '1000-01-01';
}

// Token CSRF disimpan di session, dikirim lewat input hidden, dicocokkan saat POST
function csrf_token() {
    if (empty($_SESSION['token'])) $_SESSION['token'] = bin2hex(random_bytes(32));
    return $_SESSION['token'];
}
function csrf_field() { return '<input type="hidden" name="token" value="' . e(csrf_token()) . '">'; }
function csrf_ok() {
    return isset($_POST['token'], $_SESSION['token']) && is_string($_POST['token'])
        && hash_equals($_SESSION['token'], $_POST['token']);
}

function semua_mk($conn) {
    return $conn->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')->fetch_all(MYSQLI_ASSOC);
}

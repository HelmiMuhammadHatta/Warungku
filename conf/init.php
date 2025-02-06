<?php
// Pastikan session hanya dimulai jika belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require("globalvar.php");

$host = "localhost";
$user = "root";
$pass = "";
$debe = "db_toko";

$koneksi = mysqli_connect($host, $user, $pass, $debe);

if (!$koneksi) {
    echo 'KONEKSI GAGAL';
}

function query($sql) {
    global $koneksi;
    $result = mysqli_query($koneksi, $sql);
    if (!$result) {
        error_log("Query error: " . mysqli_error($koneksi));
    }
    return $result;
}
function total($sql) {
    global $koneksi;
    $res = query($sql);
    return mysqli_num_rows($res);
}

function start() {
    query("START TRANSACTION");
}

function commit() {
    query("COMMIT");
}

function rollback() {
    query("ROLLBACK");
}

function getFaktur() {
    $sql = "SELECT * FROM transaksi ORDER BY trafaktur DESC LIMIT 1";
    $res = query($sql);
    $tot = total($sql);

    if ($tot == 0) {
        $no = "TRA0001";
    } else {
        $row = mysqli_fetch_assoc($res);
        $res = substr($row['trafaktur'], 3);
        $res += 1;
        $no = "TRA" . str_pad($res, 4, "0", STR_PAD_LEFT);
    }

    return $no;
}

function cek() {
    global $base_url;

    if (empty($_SESSION['user'])) {
        header("Location: {$base_url}pages/login.php");
        exit();
    }
}

function logout() {
    session_unset();
    session_destroy();
}
?>

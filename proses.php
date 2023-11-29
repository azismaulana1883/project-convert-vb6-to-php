<?php
// File: proses.php

// Fungsi validasi
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Lakukan koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_web";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangkap data dari formulir dan lakukan validasi
$vKPNo = validateInput($_POST['vKPNo']);
$vBuyerNo = validateInput($_POST['vBuyerNo']);

// Validasi data
if (empty($vKPNo) || empty($vBuyerNo)) {
    die("Harap isi KP# dan No. Buyer dengan benar.");
}

// Pengecekan data sebelum eksekusi query
$sqlCheck = "SELECT * FROM tmpexppacklist WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND shipmode='10'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck === false) {
    die("Error dalam query: " . $conn->error);
}

if ($resultCheck->num_rows > 0) {
    // Data sudah ada, tampilkan alert untuk membuat data baru
    echo '<script>';
    echo 'if (confirm("Data sudah ada. Apakah Anda ingin membuat data baru?")) {';
    echo '  document.cookie = "vKPNo=' . $vKPNo . '";';
    echo '  document.cookie = "vBuyerNo=' . $vBuyerNo . '";';
    echo '  window.location.href = "creatdata.html";';  // Ganti dengan halaman yang sesuai
    echo '} else {';
    echo '  alert("Proses pembuatan data baru dibatalkan.");';
    echo '}';
    echo '</script>';
} else {
    // Data tidak ditemukan
echo '<script>';
echo 'if (confirm("Data tidak ditemukan. Apakah Anda ingin membuat data baru?")) {';

// Set cookie untuk vKPNo dan vBuyerNo
echo '  document.cookie = "vKPNo=' . $vKPNo . '";';
echo '  document.cookie = "vBuyerNo=' . $vBuyerNo . '";';

// Tambahkan data baru ke tmpexppacklist
$sqlInsert = "INSERT INTO tmpexppacklist (kpno, buyerno, shipmode) VALUES ('$vKPNo', '$vBuyerNo', '10')";
$resultInsert = $conn->query($sqlInsert);

if ($resultInsert === false) {
    echo '  alert("Gagal membuat data baru.");';
} else {
    echo '  alert("Data baru berhasil dibuat.");';
}

echo '  window.location.href = "creatdata.html";';  // Ganti dengan halaman yang sesuai
echo '} else {';
echo '  alert("Proses pembuatan data baru dibatalkan.");';
echo '}';
echo '</script>';
}

// Tutup koneksi ke database
$conn->close();
?>

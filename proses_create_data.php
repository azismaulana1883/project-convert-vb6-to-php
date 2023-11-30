<?php
// File: proses_create_data.php

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

// Dapatkan nilai dari cookie atau tentukan nilai default
$vKPNo = isset($_COOKIE['vKPNo']) ? $_COOKIE['vKPNo'] : ''; // Ganti dengan logic sesuai kebutuhan
$vBuyerNo = isset($_COOKIE['vBuyerNo']) ? $_COOKIE['vBuyerNo'] : ''; // Ganti dengan logic sesuai kebutuhan
$vNoPackList = 1; // Ganti dengan logic sesuai kebutuhan
$vShipMode = 10; // Ganti dengan logic sesuai kebutuhan

//Ambil nilai vmax dari cookie
$vMaxPcsKarton = isset($_COOKIE['vMaxPcsKarton']) ? $_COOKIE['vMaxPcsKarton'] : '';

var_dump($vMaxPcsKarton);

// Mulai Hitung
$sqlColor = "SELECT DISTINCT color FROM sap_cfm WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' ORDER BY color";
$resultColor = $conn->query($sqlColor);

if (!$resultColor) {
    die("Error dalam query: " . $conn->error);
}

$dataPerhitungan = "";

while ($rowColor = $resultColor->fetch_assoc()) {
    $vColorNya = $rowColor['color'];

    $sqlSize = "SELECT a.*, SUM(a.qty_order) AS qty FROM sap_cfm a
                INNER JOIN mastersize s ON a.size = s.size
                WHERE a.kpno='$vKPNo' AND a.buyerno='$vBuyerNo' AND a.color='$vColorNya'
                GROUP BY a.size ORDER BY s.urut";

    $resultSize = $conn->query($sqlSize);

    if (!$resultSize) {
        die("Error dalam query: " . $conn->error);
    }

    $vCartNo = 0;
    $vCartNo2 = 0;
    $Number_Size = 1;

    while ($rowSize = $resultSize->fetch_assoc()) {
        $vQty = $rowSize['qty'];

        if (intval($vQty) >= intval($vMaxPcsKarton)) {
            $vCartNo = $vCartNo2 + 1;
            $jml_karton = 0;

            while (intval($vQty) >= intval($vMaxPcsKarton)) {
                $vQty = intval($vQty) - intval($vMaxPcsKarton);
                $vCartNo2 = $vCartNo2 + 1;
                $jml_karton = $jml_karton + 1;
            }

            if (intval($vCartNo2) === intval($vCartNo)) {
                $vCartNo3 = $vCartNo2;
            } else {
                $vCartNo3 = $vCartNo . "-" . $vCartNo2;
            }

            $vQty1 = "qty" . $Number_Size;
            $vDestNya = $rowSize['dest'];

            $sqlInsert = "INSERT INTO tmpexppacklist (kpno, " . $vQty1 . ", jml_karton, cart_no, ip, buyerno, articleno, nopacklist, shipmode, dest)
                        VALUES ('$vKPNo', $vMaxPcsKarton, $jml_karton, '$vCartNo3', '127.0.0.1', '$vBuyerNo', '$vColorNya', $vNoPackList, '$vShipMode', '$vDestNya')";
            $conn->query($sqlInsert);

            // Simpan hasil perhitungan ke dalam variable
            $dataPerhitungan .= "Color: $vColorNya, Size: {$rowSize['size']}, Qty: $vQty1, Dest: $vDestNya\n";
        }
        $Number_Size = $Number_Size + 1;
    }

    $resultSize->free();
}

$resultColor->free();

// Tutup koneksi ke database
$conn->close();

// Mengirim hasil perhitungan ke JavaScript menggunakan AJAX
echo '<script>';
echo 'var dataPerhitungan = ' . json_encode($dataPerhitungan) . ';';
echo 'alert("Proses perhitungan berhasil!\\n\\nHasil Perhitungan:\\n" + dataPerhitungan);';
echo '</script>';
?>

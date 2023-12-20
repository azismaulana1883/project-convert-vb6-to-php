<?php
// File: proses.php

// Fungsi validasi
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi Copy to tbl_pkli
function copyToTblPkli($vKPNo, $vBuyerNo) {
    // Lakukan koneksi ke database tbl_pkli
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "erp_web";

    $connTblPkli = new mysqli($servername, $username, $password, $dbname);

    // Periksa koneksi
    if ($connTblPkli->connect_error) {
        die("Koneksi ke tbl_pkli gagal: " . $connTblPkli->connect_error);
    }

    // Lakukan query untuk copy data
    global $colors; // Gunakan variabel global
    $colorsString = implode("','", $colors); // Menggabungkan
    global $selectedKarton;
    $result = explode('-', $selectedKarton);
    $id_karton = $result[0];
    global $vDestNya;
    $buyerCode = getBuyerCode($vKPNo); // Fungsi untuk mendapatkan buyercode dari sap_cfm
    $itemCode = getItem($vKPNo); // Fungsi untuk mendapatkan buyercode dari sap_cfm

    // Check apakah data sudah ada di tbl_pkli
    $check = "SELECT * FROM tbl_pkli WHERE kpno = '$vKPNo' AND buyerno='$vBuyerNo'";
    $resultCheck = $connTblPkli->query($check);

    if ($resultCheck->num_rows > 0) {
        // Hapus data lama dari tbl_pkli
        $sqlDelete = "DELETE FROM tbl_pkli WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo'";
        $connTblPkli->query($sqlDelete);
    } else {
        // Ambil semua nilai ukuran dan qty yang memiliki qty > 0 dari tmpexppacklist
    $sqlGetSizeAndQty = "SELECT DISTINCT cart_no, size1, qty1, size2, qty2, size3, qty3, size4, qty4, size5, qty5, size6, qty6, size7, qty7, size8, qty8, size9, qty9, size10, qty10
                         FROM tmpexppacklist
                         WHERE kpno='$vKPNo' AND articleno IN ('$colorsString') AND buyerno='$vBuyerNo'
                         ORDER BY CAST(SUBSTRING_INDEX(cart_no, '-', -1) AS UNSIGNED) ASC, cart_no ASC";

    $resultGetSizeAndQty = $connTblPkli->query($sqlGetSizeAndQty);

    if (!$resultGetSizeAndQty) {
        die("Error dalam mengeksekusi query get sizes and qty: " . $connTblPkli->error);
    }

    while ($row = $resultGetSizeAndQty->fetch_assoc()) {
        $cartNo = $row['cart_no'];

        // Mendapatkan nilai awal dan akhir dari rentang
        list($start, $end) = explode('-', $cartNo);
        $start = (int)$start;
        $end = (int)$end;

        // Mengganti jumlah duplikasi sesuai dengan rentang
        $duplicateCount = $end - $start + 1;

        // Duplikasi setiap baris sebanyak yang dibutuhkan
        for ($i = 0; $i < $duplicateCount; $i++) {
            // Gunakan setiap ukuran untuk memasukkan nilai ke dalam tbl_pkli
            $sqlCopy = "INSERT INTO tbl_pkli (kpno, no_karton_range, buyerno, color, size, buyercode, item, dest, id_jenis_karton) 
                        SELECT kpno, cart_no, buyerno, articleno, size1, '$buyerCode', '$itemCode', '$vDestNya', '$id_karton'
                        FROM tmpexppacklist 
                        WHERE kpno='$vKPNo' AND articleno IN ('$colorsString') AND buyerno='$vBuyerNo' AND cart_no='$cartNo'";
            $resultCopy = $connTblPkli->query($sqlCopy);

            // Tambahkan penanganan kesalahan jika perlu
            if (!$resultCopy) {
                die("Error dalam mengeksekusi query copy to tbl_pkli: " . $connTblPkli->error);
            }
        }
    }

    // Setelah semua data disalin, tambahkan nomor karton secara unik
    $sqlUpdateNoKarton = "UPDATE tbl_pkli SET no_karton = (@counter := @counter + 1) WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND no_karton_range IS NOT NULL";
    $connTblPkli->query("SET @counter = 0"); // Inisialisasi counter
    $connTblPkli->query($sqlUpdateNoKarton);
    }

    // Tutup koneksi ke database tbl_pkli
    $connTblPkli->close();
}



// Fungsi untuk mendapatkan buyercode dari sap_cfm
function getBuyerCode($kpno) {
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

    // Lakukan query untuk mendapatkan buyercode dari sap_cfm
    $sql = "SELECT buyercode FROM sap_cfm WHERE kpno='$kpno'";
    $result = $conn->query($sql);

    if (!$result) {
        die("Error dalam mengeksekusi query getBuyerCode: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $buyerCode = $row['buyercode'];

    // Tutup koneksi ke database
    $conn->close();

    return $buyerCode;
}

function getItem($kpno) {
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

    // Lakukan query untuk mendapatkan Item dari sap_cfm
    $sql = "SELECT item FROM sap_cfm WHERE kpno='$kpno'";
    $result = $conn->query($sql);

    if (!$result) {
        die("Error dalam mengeksekusi query getItem: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $itemCode = $row['item'];

    // Tutup koneksi ke database
    $conn->close();

    return $itemCode;
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
$vKPNo = isset($_POST['vKPNo']) ? validateInput($_POST['vKPNo']) : '';
$vBuyerNo = isset($_POST['vBuyerNo']) ? validateInput($_POST['vBuyerNo']) : '';

// Validasi data
if (empty($vKPNo) || empty($vBuyerNo)) {
    die("Harap isi KP# dan No. Buyer dengan benar.");
}

// Tangkap data dari formulir
$vMaxPcsKarton = isset($_POST['vMaxPcsKarton']) ? validateInput($_POST['vMaxPcsKarton']) : '';
$selectedSize = isset($_POST['size']) ? validateInput($_POST['size']) : '';

$selectedKarton = isset($_POST['karton']) ? $_POST['karton'] : '';

$pecah = explode("-", $selectedKarton);

$jenis_karton = $pecah[3];

if (!is_numeric($vMaxPcsKarton)) {
    echo '<script>';
    echo 'alert("Wrong qty max pcs per carton");';
    echo 'window.location.href = "index.php";';  // Ganti dengan halaman error yang sesuai
    echo '</script>';
} else {
    // Pengecekan data sebelum eksekusi query
    $sqlCheck = "SELECT * FROM tmpexppacklist WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND shipmode='10'";
    echo "Query Check: $sqlCheck";
    $resultCheck = $conn->query($sqlCheck);

    if ($resultCheck === false) {
        die("Error dalam query: " . $conn->error);
    }

    if ($resultCheck->num_rows > 0) {
        // Data sudah ada, tampilkan konfirmasi untuk membuat data baru
        echo '<script>';
        echo 'if (confirm("Data sudah ada. Apakah Anda ingin membuat data baru?")) {';

        // Hapus data yang sudah ada dari database
        $sqlDelete = "DELETE FROM tmpexppacklist WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo'";
        $conn->query($sqlDelete);

        // Setelah menghapus, set cookie dan redirect
        echo '  document.cookie = "vKPNo=' . $vKPNo . '";';
        echo '  document.cookie = "vBuyerNo=' . $vBuyerNo . '";';
        echo '  document.cookie = "vMaxPcsKarton=' . $vMaxPcsKarton . '";';
        echo '  window.location.href = "index.php";';  // Ganti dengan halaman yang sesuai
        echo '} else {';
        echo '  alert("Proses pembuatan data baru dibatalkan.");';
        echo '}';
        echo '</script>';
    } else {
        // Data tidak ditemukan
        // Set cookie untuk vKPNo dan vBuyerNo
        setcookie("vKPNo", $vKPNo, time() + (86400 * 30), "/");
        setcookie("vBuyerNo", $vBuyerNo, time() + (86400 * 30), "/");
        setcookie("vMaxPcsKarton", $vMaxPcsKarton, time() + (86400 * 30), "/");

        if ($resultCheck === false) {
            echo '<script>';
            echo 'alert("Gagal membuat data baru.");';
            echo '</script>';
        } else {
            
            // Inisialisasi lvExpPackList
            $lvExpPackList = array();

            // Setelah inisialisasi $lvExpPackList
$lvExpPackList = [];
$A = 1; // Definisikan $A di sini
            // Mulai Hitung
            $sqlColor = "SELECT DISTINCT color FROM sap_cfm WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' ORDER BY color";
            echo "Query Color: $sqlColor";
            $resultColor = $conn->query($sqlColor);
            var_dump($resultColor);

            if (!$resultColor) {
                die("Error dalam query: " . $conn->error);
            }

            $dataPerhitungan = "";

                        //Start Perhitungan
            while ($rowColor = $resultColor->fetch_assoc()) {
                $vColorNya = $rowColor['color'];
                $colors = array(); // Inisialisasi array color
                echo "Inside first loop: $vColorNya";

                // Simpan nilai color dalam array
                $colors[] = $vColorNya;
                echo "Before query execution";
                $sqlSize = "SELECT a.*, SUM(a.qty_order) AS qty FROM sap_cfm a
                            INNER JOIN mastersize s ON a.size = s.size
                            WHERE a.kpno='$vKPNo' AND a.buyerno='$vBuyerNo' AND a.color='$vColorNya'
                            GROUP BY a.size ORDER BY s.urut";
                $resultSize = $conn->query($sqlSize);
                var_dump($resultSize);
                
                if ($resultSize === false) {
                    die("Error dalam query: " . $conn->error);
                }
                echo "After query execution";

                if (!$resultSize) {
                    die("Error dalam query: " . $conn->error);
                }

                $vCartNo = 0;
                $vCartNo2 = 0;
                $Number_Size = 1;

                while ($rowSize = $resultSize->fetch_assoc()) {
                    $vQty = $rowSize['qty'];
                    $vSize = $rowSize['size']; // Ambil informasi ukuran

                    if (intval($vQty) >= intval($vMaxPcsKarton)) {
                        $vCartNo = $vCartNo2 + 1;
                        $jml_karton = 0;

                        // Hitung sisaan dengan menyimpan nilai desimal
                        $sisaan = fmod($vQty, $vMaxPcsKarton);

                        // Simpan nilai sisaan ke dalam lvExpPackList
                        $lvExpPackList[] = $sisaan;

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
                        $vNoPackList = 0; // Tentukan nilai default atau sesuai kebutuhan
                        $vShipMode = 10; // Tentukan nilai default atau sesuai kebutuhan
                        // global $vMaxPcsKarton;

                        $sqlInsert = "INSERT INTO tmpexppacklist (kpno, " . $vQty1 . ", jml_karton, cart_no, ip, buyerno, articleno, nopacklist, shipmode, dest, jenis_karton,maxpcs)

                            VALUES ('$vKPNo', $vMaxPcsKarton, $jml_karton, '$vCartNo3', '127.0.0.1', '$vBuyerNo', '$vColorNya', $vNoPackList, '$vShipMode', '$vDestNya', '$jenis_karton','$vMaxPcsKarton')";

                        var_dump("Sql Insert Mulai Hitung",$sqlInsert);

                        $conn->query($sqlInsert);
                        var_dump("Sql Insert Setelah Eksekusi", $sqlInsert);

                        if ($conn->error) {
                            echo "Error executing query: " . $conn->error;
                        } else {
                            echo "Query executed successfully!";
                        }
                        // Update nilai size pada tabel tmpexppacklist
                        $vSizeNya = "size" . $Number_Size;
                        var_dump("Value of vSizeNya:", $vSizeNya);
                        $sqlUpdateSize = "UPDATE tmpexppacklist SET $vSizeNya = '$vSize' WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND articleno='$vColorNya'";
                        echo "SQL Update Query: $sqlUpdateSize";
                        $resultUpdateSize = $conn->query($sqlUpdateSize);

                        if (!$resultUpdateSize) {
                            die("Error dalam mengeksekusi query update size: " . $conn->error);
                        }

                        // Simpan hasil perhitungan ke dalam variable
                        $dataPerhitungan .= "Color: $vColorNya, Size: {$rowSize['size']}, Qty: $vQty1, Dest: $vDestNya\n";
                    }
                    // Simpan nilai size ke dalam $LvSize
        $LvSize[] = ['Text' => $rowSize['size'], 'Value' => $rowSize['size']];
        $A++;
                    $Number_Size = $Number_Size + 1;
                }

                $resultSize->free();
            }
//Sisaan
$A = 1;
while ($A <= count($lvExpPackList)) {
    echo "Inside second loop: $A";

    if (isset($lvExpPackList[$A - 1])) {
        $vMaxPcsKartonSisa = $lvExpPackList[$A - 1]; // Perbaikan typo di sini

        if (intval($vMaxPcsKartonSisa) > 0) {
            $vCartNo2++;

            $vQty1 = "qty" . $A;
            $sqlInsertSisaan = "INSERT INTO tmpexppacklist (kpno, $vQty1, jml_karton, cart_no, ip, buyerno, articleno, nopacklist, shipmode, dest, jenis_karton, maxpcs)
                VALUES ('$vKPNo', $vMaxPcsKartonSisa, 1, '$vCartNo2', '127.0.0.1', '$vBuyerNo', '$vColorNya', $vNoPackList, '$vShipMode', '$vDestNya', '$jenis_karton', '$vMaxPcsKarton')";

            var_dump("sql insert sisaan", $sqlInsertSisaan);

            $conn->query($sqlInsertSisaan);

            if ($conn->error) {
                echo "Error executing query: " . $conn->error;
            } else {
                echo "Query executed successfully!";
            }

            // Update nilai size pada tabel tmpexppacklist
            $vSizeNya = "size" . $A;
            $sqlUpdateSizeSisaan = "UPDATE tmpexppacklist SET $vSizeNya = '{$LvSize[$A - 1]['Text']}' WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND articleno='$vColorNya' AND cart_no='$vCartNo2'";
            var_dump("size sisaan", $sqlUpdateSizeSisaan);
            $resultUpdateSizeSisaan = $conn->query($sqlUpdateSizeSisaan);

            if (!$resultUpdateSizeSisaan) {
                die("Error dalam mengeksekusi query update size sisaan: " . $conn->error);
            }
        }
    }

    $A++;
}
           // Gunakan $LvSize dalam loop untuk update size pada tabel tmpexppacklist
$A = 1;
while ($A <= count($LvSize)) {
    if (isset($LvSize[$A - 1])) {
        $vSz = "size" . $A;
        $IPx = '127.0.0.1';
        $vShipMode = 10;

        $vSizeText = $LvSize[$A - 1]['Text'];

        $sql = "UPDATE tmpexppacklist SET $vSz = '$vSizeText' WHERE ip='$IPx'" .
               " AND kpno='$vKPNo' AND buyerno='$vBuyerNo' AND articleno='$vColorNya' AND shipmode='" . ($vShipMode === "" ? $vShipModeWIP : $vShipMode) . "'";

        var_dump("SQL UPDATE", $sql);

        // Eksekusi query ke database PHP di sini
        $result = mysqli_query($conn, $sql);

        // Tambahkan penanganan kesalahan jika perlu
        if (!$result) {
            die("Error dalam mengeksekusi query: " . mysqli_error($conn));
        } else {
            // Log bahwa elemen ditemukan dan diupdate
            echo "SQL UPDATE berhasil: " . $sql . "\n";
        }
    } else {
        // Log atau tanggapi bahwa elemen tidak ditemukan di $LvSize
        echo "Elemen dengan indeks " . ($A - 1) . " tidak ditemukan di dalam \$LvSize\n";
        break; // Hentikan loop
    }
    $A++;
}


            $resultColor->free();

            copyToTblPkli($vKPNo, $vBuyerNo);

            // Tutup koneksi ke database
            $conn->close();

            // Mengirim hasil perhitungan ke JavaScript menggunakan AJAX
            echo '<script>';
            echo 'var dataPerhitungan = ' . json_encode($dataPerhitungan) . ';';
            echo 'alert("Proses perhitungan berhasil!\\n\\nHasil Perhitungan:\\n" + dataPerhitungan);';
            echo '</script>';
        }
    }
}
?>
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
    $colorsString = implode("','", $colors); // Menggabungkan nilai array menjadi string terpisah oleh koma
    $sqlCopy = "INSERT INTO tbl_pkli (kpno, buyerno, color) 
            SELECT kpno, buyerno, articleno 
            FROM tmpexppacklist 
            WHERE kpno='$vKPNo' AND articleno IN ('$colorsString') AND buyerno='$vBuyerNo'";
    $resultCopy = $connTblPkli->query($sqlCopy);

    // Tambahkan penanganan kesalahan jika perlu
    if (!$resultCopy) {
        die("Error dalam mengeksekusi query copy to tbl_pkli: " . $connTblPkli->error);
    }

    // Tutup koneksi ke database tbl_pkli
    $connTblPkli->close();
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

    $jenis_karton = $pecah[2];

    if (!is_numeric($vMaxPcsKarton)) {
        echo '<script>';
        echo 'alert("Wrong qty max pcs per carton");';
        echo 'window.location.href = "index.php";';  // Ganti dengan halaman error yang sesuai
        echo '</script>';
    } else {
        // Pengecekan data sebelum eksekusi query
        $sqlCheck = "SELECT * FROM tmpexppacklist WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND shipmode='10'";
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

            // Tambahkan data baru ke tmpexppacklist
            $sqlInsert = "INSERT INTO tmpexppacklist (kpno, buyerno, shipmode) VALUES ('$vKPNo', '$vBuyerNo', '10')";
            $resultInsert = $conn->query($sqlInsert);

            if ($resultInsert === false) {
                echo '<script>';
                echo 'alert("Gagal membuat data baru.");';
                echo '</script>';
            } else {
                // Mulai Hitung
                $sqlColor = "SELECT DISTINCT color FROM sap_cfm WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' ORDER BY color";
                $resultColor = $conn->query($sqlColor);

                if (!$resultColor) {
                    die("Error dalam query: " . $conn->error);
                }

                $dataPerhitungan = "";
                
                //Start Perhitungan
                while ($rowColor = $resultColor->fetch_assoc()) {
                $vColorNya = $rowColor['color'];
                $colors = array(); // Inisialisasi array color

                // Simpan nilai color dalam array
                $colors[] = $vColorNya;
                var_dump($colors);

                    $sqlSize = "SELECT a.*, SUM(a.qty_order) AS qty FROM sap_cfm a
                                INNER JOIN mastersize s ON a.size = s.size
                                WHERE a.kpno='$vKPNo' AND a.buyerno='$vBuyerNo' AND a.color='$vColorNya'
                                GROUP BY a.size ORDER BY s.urut";
                    var_dump($sqlSize);
                    $resultSize = $conn->query($sqlSize);

                    if ($resultSize === false) {
                        die("Error dalam query: " . $conn->error);
                    }

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
                            global $vmaxPcsKarton;

                            $sqlInsert = "INSERT INTO tmpexppacklist (kpno, " . $vQty1 . ", jml_karton, cart_no, ip, buyerno, articleno, nopacklist, shipmode, dest, jenis_karton,maxpcs)

                                VALUES ('$vKPNo', $vMaxPcsKarton, $jml_karton, '$vCartNo3', '127.0.0.1', '$vBuyerNo', '$vColorNya', $vNoPackList, '$vShipMode', '$vDestNya', '$jenis_karton','$vMaxPcsKarton')";
                                
                            var_dump($sqlInsert);
                            $conn->query($sqlInsert);

                            if ($conn->error) {
                            echo "Error executing query: " . $conn->error;
                            } else {
                            echo "Query executed successfully!";
                            }
                            // Update nilai size pada tabel tmpexppacklist
    $vSizeNya = "size" . $Number_Size;
    $sqlUpdateSize = "UPDATE tmpexppacklist SET $vSizeNya = '$vSize' WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' AND articleno='$vColorNya'";
    $resultUpdateSize = $conn->query($sqlUpdateSize);

    if (!$resultUpdateSize) {
        die("Error dalam mengeksekusi query update size: " . $conn->error);
    }

                            // Simpan hasil perhitungan ke dalam variable
                            $dataPerhitungan .= "Color: $vColorNya, Size: {$rowSize['size']}, Qty: $vQty1, Dest: $vDestNya\n";
                        }
                        $Number_Size = $Number_Size + 1;
                    }

                    $resultSize->free();
                }
                // SISAAN
$lvExpPackList = []; // Gantilah ini dengan data yang sesuai
$A = 1;
while ($A <= count($lvExpPackList)) {
    $Number_Size = $A;
    $vMaxPcsKartonSisa = $lvExpPackList[$A]['SubItems'][1];

    if (intval($lvExpPackList[$A]['SubItems'][1]) > 0) {
        $vCartNo2++;
    }

    $vCartNo = 1;
    if (intval($lvExpPackList[$A]['SubItems'][1]) > 0) {
        $vQty1 = "qty" . $Number_Size;
        $sql = "INSERT INTO tmpexppacklist (kpno, " . $vQty1 . ", jml_karton, cart_no, ip, buyerno, articleno, nopacklist, shipmode, dest) VALUES ('" . $vKPNo . "', " .
            intval($vMaxPcsKartonSisa) . ", " . $vCartNo . ", '" . $vCartNo2 . "', '" . $IPx . "', '" . $vBuyerNo . "', '" . $vColorNya . "', " . $vNoPackList . ", '" .
            ($vShipMode === "" ? $vShipModeWIP : $vShipMode) . "', '" . $vDestNya . "')";

        // Eksekusi query ke database PHP di sini
        $result = mysqli_query($DB_Web, $sql);

        // Tambahkan penanganan kesalahan jika perlu
        if (!$result) {
            die("Error dalam mengeksekusi query: " . mysqli_error($DB_Web));
        }
    }

    $A++;
}
$LvSize = []; // Gantilah ini dengan data yang sesuai
$A = 1;
while ($A <= count($LvSize)) {
    $vSz = "size" . $A;
    $sql = "UPDATE tmpexppacklist SET " . $vSz . "='" . $LvSize[$A]['Text'] . "' WHERE ip='" . $IPx . "'" .
        " AND kpno='" . $vKPNo . "' AND buyerno='" . $vBuyerNo . "' AND articleno='" . $vColorNya . "' AND shipmode='" . ($vShipMode === "" ? $vShipModeWIP : $vShipMode) . "'";

    // Eksekusi query ke database PHP di sini
    $result = mysqli_query($DB_Web, $sql);

    // Tambahkan penanganan kesalahan jika perlu
    if (!$result) {
        die("Error dalam mengeksekusi query: " . mysqli_error($DB_Web));
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
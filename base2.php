function copyToPKLI($vKPNo, $vBuyerNo, $LvSize)
{
    global $conn;

    $sql = "DELETE FROM tbl_pkli WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo'";
    $conn->query($sql);

    $sql = "SELECT * FROM tmpexppacklist WHERE kpno='$vKPNo' AND buyerno='$vBuyerNo' ORDER BY id";
    $Rs1 = $conn->query($sql);
    $vBuyerCode = getBuyerCode($vKPNo);
    $vJmlSize = count($LvSize);

    while ($row = $Rs1->fetch_assoc()) {
        global $selectedKarton;
        $result = explode('-', $selectedKarton);
        $vid_jenis_karton = $result[0];

        for ($aa = 1; $aa <= $vJmlSize; $aa++) {
            $vNmFldSz = "size" . $aa;
            $vNmFldQty = "qty" . $aa;

            $sql = "SELECT buyercode, item FROM sap_cfm WHERE kpno='{$row['kpno']}' AND buyerno='{$row['buyerno']}' "
                . "AND color='{$row['articleno']}' AND size='{$row[$vNmFldSz]}'";

            $Rs2 = $conn->query($sql);
            $vBuyerCode = $Rs2->fetch_assoc()['buyercode'];
            $vItem = $Rs2->fetch_assoc()['item'];
            $Rs2->close();

            if (strpos($row['cart_no'], '-') !== false) {
                $vRange = "Y";
                $vSplitCtn = explode("-", $row['cart_no']);
                $loop_for = $vSplitCtn[0];
                $loop_next = $vSplitCtn[1];
            } else {
                $vRange = "N";
                $loop_for = $row['cart_no'];
                $loop_next = $row['cart_no'];
            }

            for ($ss = $loop_for; $ss <= $loop_next; $ss++) {
                $vCtnRange = ($vRange === "N") ? null : $row['cart_no'];

                if (intval($row[$vNmFldQty]) > 0) {
                    $sql = "INSERT INTO tbl_pkli (no_karton, no_karton_range, buyercode, kpno, buyerno, dest, id_jenis_karton, "
                        . "item, color, size, qty_pack) VALUES ('$ss', '$vCtnRange', '$vBuyerCode', '{$row['kpno']}', "
                        . "'{$row['buyerno']}', '{$row['dest']}', '$vid_jenis_karton', '$vItem', '{$row['articleno']}', "
                        . "'{$row[$vNmFldSz]}', '{$row[$vNmFldQty]}')";
                    $conn->query($sql);
                }
            }
        }
    }

    $Rs1->close();
    $sql = "UPDATE tbl_pkli SET no_karton_range=null WHERE no_karton_range='' AND kpno='$vKPNo' AND buyerno='$vBuyerNo'";
    $conn->query($sql);
}


// Fungsi untuk mendapatkan jumlah size dari tabel tbl_size
function GetSizeCount($conn)
{
    $sqlGetSizeCount = "SELECT COUNT(*) as sizeCount FROM tbl_size";
    $resultSizeCount = mysqli_query($conn, $sqlGetSizeCount);

    if ($resultSizeCount) {
        $rowSizeCount = mysqli_fetch_assoc($resultSizeCount);
        return $rowSizeCount['sizeCount'];
    }

    return 0;
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
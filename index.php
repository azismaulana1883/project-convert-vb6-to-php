<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengolahan Data</title>
</head>
<body>
    <h2>Form Pengolahan Data</h2>
    <form action="proses.php" method="post">
        <label for="vKPNo">KP#:</label>
        <input type="text" name="vKPNo" id="vKPNo" required>
        <br>
        <label for="vBuyerNo">Po. Buyer:</label>
        <input type="text" name="vBuyerNo" id="vBuyerNo" required>
        <br>
        <label for="vMaxPcsKarton">Masukkan Max Pcs Per Karton:</label>
        <input type="text" name="vMaxPcsKarton" id="vMaxPcsKarton" required>
        <br>
        <label for="choose_karton">Choose a karton:</label>
        <select name="karton" id="vKarton">
            <?php
            // Ambil data karton dari database
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "erp_web";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Koneksi gagal: " . $conn->connect_error);
            }

            $sqlKarton = "SELECT buyer, jenis_karton, berat_karton, cbm_karton FROM tbl_karton";
            $resultKarton = $conn->query($sqlKarton);

            if ($resultKarton->num_rows > 0) {
                while ($rowKarton = $resultKarton->fetch_assoc()) {
                    echo '<option value="' . $rowKarton['buyer'] . '-' . $rowKarton['jenis_karton'] . '-' . $rowKarton['berat_karton'] . '-' . $rowKarton['cbm_karton'] . '">'
                        . 'Buyer: ' . $rowKarton['buyer'] . ', Jenis Karton: ' . $rowKarton['jenis_karton'] . ', Berat Karton: ' . $rowKarton['berat_karton'] . ', CBM Karton: ' . $rowKarton['cbm_karton']
                        . '</option>';
                }
            }

            $conn->close();
            ?>
        </select>
        <br>
        <label for="choose_size">Choose a size:</label>
        <select name="size" id="vSize">
            <?php
            // Ambil data ukuran dari database
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "erp_web";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Koneksi gagal: " . $conn->connect_error);
            }

            $sqlSize = "SELECT size FROM mastersize";
            $resultSize = $conn->query($sqlSize);

            if ($resultSize->num_rows > 0) {
                while ($rowSize = $resultSize->fetch_assoc()) {
                    echo '<option value="' . $rowSize['size'] . '">' . $rowSize['size'] . '</option>';
                }
            }

            $conn->close();
            ?>
        </select>
        <br>
        <button type="submit">Proses Data</button>
    </form>
</body>
</html>

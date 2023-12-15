<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengolahan Data</title>
    <!-- Tambahkan link Bootstrap CSS di sini -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Form Pengolahan Data</h2>
        <form action="proses.php" method="post">
            <div class="form-group">
                <label for="vKPNo">KP.NO:</label>
                <input type="text" class="form-control" name="vKPNo" id="vKPNo" required>
            </div>
            <div class="form-group">
                <label for="vBuyerNo">Po. Buyer:</label>
                <input type="text" class="form-control" name="vBuyerNo" id="vBuyerNo" required>
            </div>
            <div class="form-group">
                <label for="vMaxPcsKarton">Masukkan Max Pcs Per Karton:</label>
                <input type="text" class="form-control" name="vMaxPcsKarton" id="vMaxPcsKarton" required>
            </div>
            <div class="form-group">
    <label for="choose_karton">Choose a karton:</label>
    <select class="form-control" name="karton" id="vKarton">
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

        $sqlKarton = "SELECT id_karton, buyer, jenis_karton, berat_karton, cbm_karton FROM tbl_karton";
        $resultKarton = $conn->query($sqlKarton);

        if ($resultKarton->num_rows > 0) {
            while ($rowKarton = $resultKarton->fetch_assoc()) {
                echo '<option value="' . $rowKarton['id_karton'] . '-' . $rowKarton['buyer'] . '-' . $rowKarton['jenis_karton'] . '-' . $rowKarton['berat_karton'] . '-' . $rowKarton['cbm_karton'] . '">'
                    . 'id_karton: ' . $rowKarton['id_karton']
                    . ', Buyer: ' . $rowKarton['buyer'] . ', Jenis Karton: ' . $rowKarton['jenis_karton'] . ', Berat Karton: ' . $rowKarton['berat_karton'] . ', CBM Karton: ' . $rowKarton['cbm_karton']
                    . '</option>';
            }
        }

        $conn->close();
        ?>
    </select>
</div>

            <button type="submit" class="btn btn-primary">Proses Data</button>
        </form>
    </div>

    <!-- Tambahkan script Bootstrap JS di sini (opsional) -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>

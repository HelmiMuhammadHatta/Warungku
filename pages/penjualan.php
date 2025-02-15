<!-- 
    created by warungku
    @ 2025
 -->
 <?php
require('../conf/init.php');
cek();

// Pastikan $_SESSION['cart'] sudah ada agar tidak error
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$msg = ""; // Hindari error Undefined variable $msg
$tanggal = date("Y-m-d");
$trapelanggan = isset($_POST['trapelanggan']) ? trim($_POST['trapelanggan']) : "";

// Ambil data cart dengan cara aman
$cart = $_SESSION['cart'];

if (isset($_POST['simpan'])) {
    $trafaktur = getFaktur();
    $user = $_SESSION['user'];
    $userid = $user['userid'];
    $err = 0;
    $grandtotal = 0;
    $error_message = "";

    // Validasi input
    if (empty($trapelanggan)) {
        $msg = 'Harap masukkan nama pelanggan';
    } elseif (count($cart) == 0) {
        $msg = 'Keranjang Kosong';
    } else {
        try {
            start();

            // Debug info
            error_log("Starting transaction with faktur: " . $trafaktur);
            error_log("Customer: " . $trapelanggan);
            error_log("Cart items: " . print_r($cart, true));

            // Insert detail transaksi
            foreach ($cart as $item) {
                $proid = mysqli_real_escape_string($koneksi, $item['proid']);
                $harga = floatval($item['proharga']);
                $jumlah = floatval($item['jumlah']);
                $sub = $harga * $jumlah;

                $sql = "INSERT INTO transaksi_detail (trafaktur, proid, tdjumlah, tdharga, tdsubtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($koneksi, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'siddd', $trafaktur, $proid, $jumlah, $harga, $sub);
                    if (!mysqli_stmt_execute($stmt)) {
                        $error_message .= "Error detail transaksi: " . mysqli_stmt_error($stmt) . "\n";
                        $err++;
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message .= "Prepare statement error: " . mysqli_error($koneksi) . "\n";
                    $err++;
                }

                $grandtotal += $sub;
            }

            // Insert header transaksi
            if ($err == 0) {
                $sql = "INSERT INTO transaksi (trafaktur, tratanggal, trapelanggan, tratotal, userid) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($koneksi, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sssdi', $trafaktur, $tanggal, $trapelanggan, $grandtotal, $userid);
                    if (!mysqli_stmt_execute($stmt)) {
                        $error_message .= "Error header transaksi: " . mysqli_stmt_error($stmt) . "\n";
                        $err++;
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message .= "Prepare statement error: " . mysqli_error($koneksi) . "\n";
                    $err++;
                }
            }

            // Commit atau rollback berdasarkan status error
            if ($err == 0) {
                commit();
                unset($_SESSION['cart']);
                $msg = 'Penjualan berhasil disimpan';
                error_log("Transaction committed successfully: " . $trafaktur);
            } else {
                rollback();
                $msg = 'Gagal menyimpan transaksi: ' . $error_message;
                error_log("Transaction rolled back due to errors: " . $error_message);
            }

        } catch (Exception $e) {
            rollback();
            $msg = 'Error sistem: ' . $e->getMessage();
            error_log("System error during transaction: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Penjualan - warungku</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css?h=3c16114e461561544db42dd299b535e5">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.min.css?h=d41d8cd98f00b204e9800998ecf8427e">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-shopping-cart"></i></div>
                    <div class="sidebar-brand-text mx-3"><span>warungku</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="nav navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item" role="presentation"><a class="nav-link" href="../index.php"><i class="fas fa-tachometer-alt" style="font-size: 20px;"></i><span style="margin-left: 10px;font-size: 18px;">Dashboard</span></a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="barang.php"><i class="icon-layers" style="font-size: 20px;"></i><span style="margin-left: 10px;font-size: 18px;">Data Barang</span></a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link active" href="penjualan.php"><i class="icon-basket" style="font-size: 20px;"></i><span style="margin-left: 10px;font-size: 18px;">Penjualan</span></a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="laporan.php"><i class="icon-list" style="font-size: 20px;"></i><span style="font-size: 18px;margin-left: 10px;">Laporan</span></a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" href="about.php"><i class="icon-info" style="font-size: 20px;"></i><span style="font-size: 18px;margin-left: 10px;">Tentang warungku</span></a></li>
                    <li class="nav-item" role="presentation"></li>
                </ul>
                                <div class="text-center d-none d-md-inline">
                    <div class="row">
                        <div class="col"><a href="logout.php" class="btn btn-primary" style="background-color: #e74a3b;"><i class="icon-logout" style="margin-right: 10px;font-size: 18px;"></i>Logout</a></div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"></nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Penjualan</h3>
                    <?= $msg ? '<h6 class="text-dark mb-4">'.$msg.'</h6>' : ""?>
                    <form method="POST">
                        <div class="row">
                            <div class="col">
                                <div class="card shadow">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Detail Penjualan</p>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group"><label for="address"><strong>Nama Pelanggan</strong><br></label><input class="form-control" type="text" placeholder="Nama Pelanggan" value="<?= $trapelanggan ?>" name="trapelanggan"></div>
                                        <div class="form-row">
                                            <div class="col">
                                                <div class="form-group"><label for="city"><strong>No Faktur</strong></label><input class="form-control" type="text" name="trafaktur" disabled value=<?= getFaktur() ?>></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group"><label for="country"><strong>Tanggal</strong></label><input class="form-control" type="text" disabled value=<?= $tanggal ?>></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Data Keranjang</p>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    $cart = $_SESSION['cart'];
                                                    $total = 0 ;

                                                    if ($cart){
                                                        foreach($cart as $i => $val){
                                                            ?>
                                                                <tr>
                                                                    <td><?= $val['pronama'] ?></td>
                                                                    <td><?= number_format($val['jumlah'],2) ?></td>
                                                                    <td><?= number_format($val['proharga'],2) ?></td>
                                                                    <td><a href="penjualan-hapus.php?p=<?= $val['proid']?>" class="btn btn-primary" style="background-color: #e74a3b;">Hapus</a></td>
                                                                </tr>
                                                            <?php
                                                            $total += $val['proharga'] * $val['jumlah'] ;
                                                        }
                                                    }

                                                    ?>
                                                    <tr></tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td>Jumlah Pembelanjaan : <?= number_format($total,2) ?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col"><a href="penjualan-pilih.php" class="btn btn-primary" style="width: 100%;background-color: #36b9cc;">Pilih Produk</a></div>
                                    <div class="col"><input class="btn btn-primary" type="submit" style="width: 100%;background-color: #1cc88a;" name="simpan" value="Simpan" /></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright"><span>Copyright © warungku 2025</span></div>
                </div>
            </footer>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="../assets/js/script.min.js?h=b86d882c5039df370319ea6ca19e5689"></script>
</body>

</html>
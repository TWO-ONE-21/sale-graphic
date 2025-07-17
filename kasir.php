<?php
// Memulai session dan menyertakan file koneksi.
session_start();
require 'config/koneksi.php';

// --- BAGIAN KEAMANAN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data pengguna dari session.
$user_id = $_SESSION['user_id'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$peran = $_SESSION['peran'];

// --- BAGIAN PEMROSESAN FORM ---
$success_message = '';
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if (!empty($product_id) && $quantity > 0) {
        $stmt = $koneksi->prepare("CALL sp_catat_penjualan(?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $product_id, $quantity);
        if ($stmt->execute()) {
            $success_message = "Penjualan berhasil dicatat! Stok produk telah diperbarui.";
        } else {
            $error_message = "Terjadi kesalahan: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Harap pilih produk dan masukkan jumlah yang valid.";
    }
}

// --- BAGIAN PENGAMBILAN DATA UNTUK DASHBOARD MINI ---
// 1. Mengambil jumlah transaksi hari ini oleh kasir yang login
$total_transaksi_hari_ini = 0;
$stmt_transaksi = $koneksi->prepare("SELECT COUNT(id) as total FROM penjualan WHERE user_id = ? AND DATE(tgl_penjualan) = CURDATE()");
$stmt_transaksi->bind_param("i", $user_id);
$stmt_transaksi->execute();
$result_transaksi = $stmt_transaksi->get_result();
if ($result_transaksi) {
    $total_transaksi_hari_ini = $result_transaksi->fetch_assoc()['total'];
}
$stmt_transaksi->close();

// 2. Mengambil total pendapatan hari ini oleh kasir yang login
$total_pendapatan_hari_ini = 0;
$stmt_pendapatan = $koneksi->prepare("SELECT SUM(total_harga) as total FROM penjualan WHERE user_id = ? AND DATE(tgl_penjualan) = CURDATE()");
$stmt_pendapatan->bind_param("i", $user_id);
$stmt_pendapatan->execute();
$result_pendapatan = $stmt_pendapatan->get_result();
if ($result_pendapatan) {
    $total_pendapatan_hari_ini = $result_pendapatan->fetch_assoc()['total'] ?? 0;
}
$stmt_pendapatan->close();


// --- BAGIAN PENGAMBILAN DATA PRODUK UNTUK DROPDOWN ---
$products = [];
$sql_products = "SELECT id, nama_produk, stok FROM produk WHERE stok > 0 ORDER BY nama_produk ASC";
$result_products = mysqli_query($koneksi, $sql_products);
if ($result_products) {
    while($row = mysqli_fetch_assoc($result_products)) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cash-register"></i> Dashboard Kasir
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Halo, <strong><?php echo htmlspecialchars($nama_lengkap); ?></strong>
                </span>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <!-- Baris untuk Widget Statistik -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-title h5">Transaksi Hari Ini</div>
                            <div class="display-4"><?php echo $total_transaksi_hari_ini; ?></div>
                        </div>
                        <i class="fas fa-shopping-cart fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-title h5">Pendapatan Hari Ini</div>
                            <div class="h3">Rp <?php echo number_format($total_pendapatan_hari_ini, 0, ',', '.'); ?></div>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baris untuk Form Utama -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus-circle"></i> Catat Transaksi Penjualan Baru</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if(!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if(!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form action="kasir.php" method="POST">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Pilih Produk</label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="" disabled selected>-- Pilih Produk --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo htmlspecialchars($product['id']); ?>">
                                            <?php echo htmlspecialchars($product['nama_produk']) . ' (Stok: ' . $product['stok'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Jumlah Terjual</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Simpan Transaksi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

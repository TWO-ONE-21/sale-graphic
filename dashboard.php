<?php
session_start();

require 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$nama_lengkap = $_SESSION['nama_lengkap'];
$peran = $_SESSION['peran'];

$labels_produk = [];
$data_penjualan = [];

if ($peran == 'admin') {
    $sql_chart = "SELECT nama_produk, total_jumlah_terjual FROM v_ringkasan_penjualan ORDER BY total_jumlah_terjual DESC";
    $result_chart = mysqli_query($koneksi, $sql_chart);

    if (mysqli_num_rows($result_chart) > 0) {
        while($row = mysqli_fetch_assoc($result_chart)) {            
            $labels_produk[] = $row['nama_produk'];
            $data_penjualan[] = $row['total_jumlah_terjual'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
 body {
            background-color: #121212; /* Latar belakang gelap */
            color: #e0e0e0; /* Teks terang */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column; /* Mengatur tata letak kolom */
            min-height: 100vh;
            margin: 0;
            /* Efek gradien halus */
            background: linear-gradient(135deg, #1e1e1e, #2a2a2a);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.05); /* Latar belakang navbar semi-transparan */
            backdrop-filter: blur(10px); /* Efek blur pada latar belakang */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2); /* Border tipis */
        }
        .navbar-brand, .navbar-text {
            color: #e0e0e0 !important;
        }
        .navbar-brand i {
            color: #bb86fc; /* Warna aksen ungu */
        }
        .btn-outline-danger {
            color: #e57373;
            border-color: #e57373;
        }
        .btn-outline-danger:hover {
            background-color: #e57373;
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e0e0e0;
        }
    </style>
</head>
<body>    
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Halo, <strong><?php echo htmlspecialchars($nama_lengkap); ?></strong> (<?php echo htmlspecialchars($peran); ?>)
                </span>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">        
        <div class="p-5 mb-4 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2);">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold" style="color: #bb86fc;">Selamat Datang di Dashboard Admin!</h1>
                <p class="col-md-8 fs-4">Di sini Anda dapat melihat ringkasan penjualan produk dan mengelola data.</p>
            </div>
        </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Grafik Ringkasan Penjualan Produk</h5>
                        </div>
                        <div class="card-body">                            
                            <canvas id="grafikPenjualan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
    </div>    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        <?php if ($peran == 'admin' && !empty($labels_produk)): ?>
        
        const ctx = document.getElementById('grafikPenjualan').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_produk); ?>,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: <?php echo json_encode($data_penjualan); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Produk Paling Laris Berdasarkan Jumlah Terjual'
                    }
                }
            }
        });

        <?php endif; ?>
    </script>    
</body>
</html>

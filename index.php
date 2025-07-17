<?php
session_start();

require 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM v_masuk WHERE username = '$username'";
    $result = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);

        if (md5($password) == $user_data['password']) {
            
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
            $_SESSION['peran'] = $user_data['peran'];

            if ($_SESSION['peran'] == 'admin') {
                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: kasir.php");
                exit();
            }


        } else {
            $error_message = "Kata sandi yang Anda masukkan salah.";
        }
    } else {
        $error_message = "Nama pengguna (username) tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #121212; /* Latar belakang gelap */
            color: #e0e0e0; /* Teks terang */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            /* Efek gradien halus */
            background: linear-gradient(135deg, #1e1e1e, #2a2a2a);
        }

        .login-card {            
            background: rgba(255, 255, 255, 0.1); /* Latar belakang kartu semi-transparan */
            border-radius: 15px;
            backdrop-filter: blur(10px); /* Efek blur pada latar belakang */
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2); /* Border tipis */
        }

        .login-card .card-title {
            color: #bb86fc; /* Warna aksen ungu */
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .login-card .form-label {
            color: #e0e0e0;
        }

        .login-card .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .login-card .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #bb86fc;
            box-shadow: 0 0 0 0.2rem rgba(187, 134, 252, 0.25);
        }

        .login-card .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
            color: #121212;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
        }

        .login-card .btn-primary:hover {
            background-color: #9c27b0;
            border-color: #9c27b0;
        }

        .login-card .alert-danger {
            background-color: rgba(229, 115, 115, 0.2);
            border-color: #e57373;
            color: #ffcdd2;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h3 class="card-title text-center">Selamat Datang</h3>
            <form action="index.php" method="POST">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Nama Pengguna</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Masuk</button>
                    </div>
            </form>            
        </div>
    </div>
</body>
</html>

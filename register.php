<?php
session_start();
include 'koneksi.php';

$error = '';
$sukses = '';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah dipakai orang lain, silakan pilih yang lain!";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', 'user')");
        $sukses = "Pendaftaran berhasil! Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Homestock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        body { background-color: #E3F2FD; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 4px 12px rgba(13, 71, 161, 0.1); }
        .btn-primary { background-color: #2196F3; border: none; }
        .btn-primary:hover { background-color: #0D47A1; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="container" style="max-width: 400px;">
        <div class="card p-4">
            <div class="text-center mb-4">
                <h3 style="color: #0D47A1; font-weight: bold;">Daftar Homestock</h3>
                <p class="text-muted small">Buat akun baru untuk mulai mencatat stok</p>
            </div>
            <?php if($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error; ?></div>
            <?php endif; ?>
            <?php if($sukses): ?>
                <div class="alert alert-success py-2 small"><?= $sukses; ?> <a href="login.php" class="fw-bold">Login sekarang</a></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-2">
                    <label class="form-label text-muted small">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label text-muted small">Username (untuk Login & Sapaan)</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="register" class="btn btn-primary w-100 py-2">Daftar Sekarang</button>
            </form>
            <div class="text-center mt-3">
                <span class="small text-muted">Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold">Login</a></span>
            </div>
        </div>
    </div>
</body>
</html>
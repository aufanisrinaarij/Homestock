<?php
session_start();
include 'koneksi.php';

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($password === $row['password'] || password_verify($password, $row['password'])) {
                $_SESSION['id_user']  = $row['id_user'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['nama']     = $row['nama'];
                $_SESSION['role']     = isset($row['role']) ? $row['role'] : 'user';
                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah untuk username '$username'!";
            }
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$username', '$username', '$password_hash', 'user')");
            
            if ($insert) {
                $_SESSION['id_user']  = mysqli_insert_id($koneksi);
                $_SESSION['username'] = $username;
                $_SESSION['nama']     = $username;
                $_SESSION['role']     = 'user';
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal memproses login otomatis.";
            }
        }
    } else {
        $error = "Username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Homestock</title>
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
                <h3 style="color: #0D47A1; font-weight: bold;">Homestock</h3>
                <p class="text-muted small">Silakan login untuk mengelola persediaan</p>
            </div>
            <?php if($error): ?>
                <div class="alert alert-danger py-2 small"><?= $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan nama / username Anda" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 py-2">Login</button>
            </form>
            <div class="text-center mt-3">
                <span class="small text-muted">Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar di sini</a></span>
            </div>
        </div>
    </div>
</body>
</html>
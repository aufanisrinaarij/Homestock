<?php
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
// Arsip otomatis: pindahkan transaksi bulan lalu ke tabel arsip
if (isset($koneksi)) {
    include_once 'arsip_otomatis.php';
    arsipkan_transaksi_lama($koneksi);
}

$hal_aktif = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homestock - Manajemen Persediaan Rumah Tangga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        body { background-color: #E3F2FD; color: #333; }
        .navbar { background-color: #0D47A1 !important; }
        .navbar-brand, .nav-link { color: #fff !important; }
        .nav-link:hover, .nav-link.active { color: #90CAF9 !important; }
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #2196F3; border: none; }
        .btn-primary:hover { background-color: #0D47A1; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-house-heart-fill"></i> Homestock</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?= $hal_aktif === 'index.php' ? 'active' : ''; ?>" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= $hal_aktif === 'barang.php' ? 'active' : ''; ?>" href="barang.php">Data Barang</a></li>
                    <li class="nav-item"><a class="nav-link <?= $hal_aktif === 'transaksi.php' ? 'active' : ''; ?>" href="transaksi.php">Transaksi Stok</a></li>
                    <li class="nav-item"><a class="nav-link <?= $hal_aktif === 'arsip.php' ? 'active' : ''; ?>" href="arsip.php">Arsip</a></li>
                    <li class="nav-item"><a class="nav-link <?= $hal_aktif === 'belanja.php' ? 'active' : ''; ?>" href="belanja.php">Daftar Belanja</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link fw-bold text-warning <?= $hal_aktif === 'pengguna.php' ? 'active' : ''; ?>" href="pengguna.php">Data Pengguna</a></li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-light btn-sm px-3 mt-1" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4">
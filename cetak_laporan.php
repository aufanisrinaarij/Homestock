<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Deteksi otomatis nama kolom jenis transaksi di database (jenis / jenis_transaksi)
$cek_kolom   = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi_stok LIKE 'jenis_transaksi'");
$kolom_jenis = (mysqli_num_rows($cek_kolom) > 0) ? 'jenis_transaksi' : 'jenis';

$q            = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$f_dari       = isset($_GET['dari']) ? $_GET['dari'] : '';
$f_sampai     = isset($_GET['sampai']) ? $_GET['sampai'] : '';

// Hanya transaksi milik user yang sedang login
$query_sql = "SELECT t.*, b.nama_barang, b.satuan FROM transaksi_stok t JOIN barang b ON t.id_barang = b.id_barang WHERE t.id_user = '$id_user'";
if ($q !== '') {
    $q_esc = mysqli_real_escape_string($koneksi, $q);
    $query_sql .= " AND (b.nama_barang LIKE '%$q_esc%' OR t.keterangan LIKE '%$q_esc%')";
}
if ($filter_jenis === 'Masuk' || $filter_jenis === 'Keluar') {
    $query_sql .= " AND t.$kolom_jenis = '$filter_jenis'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_dari)) {
    $query_sql .= " AND DATE(t.tanggal) >= '$f_dari'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_sampai)) {
    $query_sql .= " AND DATE(t.tanggal) <= '$f_sampai'";
}
$query_sql .= " ORDER BY t.tanggal DESC, t.id_transaksi DESC";
$transaksi_list = mysqli_query($koneksi, $query_sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Stok - Homestock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #000; font-family: 'Segoe UI', Arial, sans-serif; }
        h3 { color: #0D47A1; border-bottom: 3px solid #0D47A1; display: inline-block; padding-bottom: .4rem; }
        thead th { background-color: #E3F2FD !important; color: #0D47A1; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @media print {
            .no-print { display: none; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container mt-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold">HOMESTOCK - LAPORAN TRANSAKSI STOK</h3>
            <p class="text-muted small mb-0">Dicetak pada: <?= date('d-m-Y H:i'); ?></p>
            <?php
            $info = [];
            if ($q !== '') $info[] = 'Kata kunci: "' . htmlspecialchars($q) . '"';
            if ($filter_jenis === 'Masuk' || $filter_jenis === 'Keluar') $info[] = 'Jenis: ' . $filter_jenis;
            if ($f_dari !== '' || $f_sampai !== '') $info[] = 'Periode: ' . ($f_dari ?: '...') . ' s/d ' . ($f_sampai ?: '...');
            if ($info) echo '<p class="small mb-0">' . implode(' | ', $info) . '</p>';
            ?>
        </div>
        <table class="table table-bordered table-sm align-middle small">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while($t = mysqli_fetch_assoc($transaksi_list)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['tanggal'])); ?></td>
                    <td><?= htmlspecialchars($t['nama_barang']); ?></td>
                    <td><?= htmlspecialchars($t[$kolom_jenis] ?? ''); ?></td>
                    <td><?= $t['jumlah'] . ' ' . $t['satuan']; ?></td>
                    <td><?= htmlspecialchars($t['keterangan']); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($transaksi_list) === 0): ?>
                <tr><td colspan="6" class="text-center text-muted">Tidak ada data transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-end mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-sm">Cetak / Simpan PDF</button>
            <a href="transaksi.php" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
</body>
</html>
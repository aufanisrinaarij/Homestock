<?php
session_start();
include 'koneksi.php';
include 'header.php';

// Pastikan mengambil id_user dari sesi login yang aktif
$id_user  = $_SESSION['id_user'] ?? 1;
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// Kueri statistik DITAMBAHKAN filter id_user secara spesifik
$total_barang    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE id_user = '$id_user'"))['total'];
$total_menipis   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE id_user = '$id_user' AND stok <= stok_minimum"))['total'];
$total_transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi_stok WHERE id_user = '$id_user'"))['total'];

if ($is_admin) {
    $total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users"))['total'];
    $list_users  = mysqli_query($koneksi, "SELECT id_user, nama, username, role FROM users ORDER BY id_user ASC");
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 rounded-4 text-white shadow-sm" style="background-color: #2196F3;">
            <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['nama']); ?>! 👋</h2>
            <p class="mb-0">
                <?= $is_admin ? 'Anda masuk sebagai Administrator Sistem Homestock.' : 'Kelola persediaan kebutuhan rumah tangga Anda dengan mudah, cepat, dan teratur.'; ?>
            </p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="<?= $is_admin ? 'col-md-3' : 'col-md-4'; ?>">
        <div class="card p-3 border-start border-4 border-primary shadow-sm">
            <h6 class="text-muted">Total Jenis Barang</h6>
            <h3 class="fw-bold" style="color: #0D47A1;"><?= $total_barang; ?></h3>
        </div>
    </div>
    <div class="<?= $is_admin ? 'col-md-3' : 'col-md-4'; ?>">
        <div class="card p-3 border-start border-4 border-warning shadow-sm">
            <h6 class="text-muted">Stok Menipis (Perlu Dibeli)</h6>
            <h3 class="fw-bold text-danger"><?= $total_menipis; ?></h3>
        </div>
    </div>
    <div class="<?= $is_admin ? 'col-md-3' : 'col-md-4'; ?>">
        <div class="card p-3 border-start border-4 border-info shadow-sm">
            <h6 class="text-muted">Total Transaksi Bulan Ini</h6>
            <h3 class="fw-bold text-dark"><?= $total_transaksi; ?></h3>
        </div>
    </div>
    <?php if ($is_admin): ?>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-success shadow-sm">
            <h6 class="text-muted">Total Pengguna Sistem</h6>
            <h3 class="fw-bold text-success"><?= $total_users; ?></h3>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
<div class="row">
    <div class="col-12">
        <div class="card p-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: #0D47A1;"><i class="bi bi-people-fill"></i> Daftar Seluruh Pengguna Sistem</h5>
                <a href="pengguna.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Kelola / Tambah Pengguna</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead style="background-color: #E3F2FD;">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Hak Akses (Role)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($u = mysqli_fetch_assoc($list_users)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['nama']); ?></td>
                            <td><?= htmlspecialchars($u['username']); ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?= strtoupper($u['role']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
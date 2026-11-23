<?php
session_start();
include 'koneksi.php';
include 'header.php';

$id_user = $_SESSION['id_user'];

// Deteksi otomatis nama kolom jenis di tabel arsip (jenis / jenis_transaksi)
$cek_kolom   = mysqli_query($koneksi, "SHOW COLUMNS FROM arsip_transaksi LIKE 'jenis_transaksi'");
$kolom_jenis = (mysqli_num_rows($cek_kolom) > 0) ? 'jenis_transaksi' : 'jenis';

$nama_bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
               '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

// Filter bulan
$f_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$where   = "WHERE a.id_user = '$id_user'";
if (preg_match('/^\d{4}-\d{2}$/', $f_bulan)) {
    $where .= " AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$f_bulan'";
} else {
    $f_bulan = '';
}

// Daftar bulan yang punya arsip (untuk dropdown)
$daftar_bulan = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') AS bulan FROM arsip_transaksi WHERE id_user = '$id_user' ORDER BY bulan DESC");

$arsip = mysqli_query($koneksi, "SELECT a.*, b.nama_barang, b.satuan FROM arsip_transaksi a LEFT JOIN barang b ON a.id_barang = b.id_barang $where ORDER BY a.tanggal DESC");
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-archive"></i> Arsip Transaksi Bulanan</h3>
        <p class="text-muted small">Transaksi bulan-bulan sebelumnya dipindahkan otomatis ke sini setiap pergantian bulan.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card p-3 shadow-sm border-0">
            <!-- Filter Bulan -->
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        <?php while($b = mysqli_fetch_assoc($daftar_bulan)):
                            list($th, $bl) = explode('-', $b['bulan']); ?>
                            <option value="<?= $b['bulan']; ?>" <?= $f_bulan === $b['bulan'] ? 'selected' : ''; ?>><?= $nama_bulan[$bl] . ' ' . $th; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if ($f_bulan !== ''): ?>
                <div class="col-md-2">
                    <a href="arsip.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
                </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead style="background-color: #E3F2FD;">
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
                        <?php $no = 1; while($row = mysqli_fetch_assoc($arsip)): ?>
                        <?php
                            $jenis_row = $row[$kolom_jenis] ?? '';
                            $waktu     = strtotime($row['tanggal']);
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="text-nowrap">
                                <?= date('d/m/Y', $waktu); ?>
                                <?php if (date('H:i', $waktu) !== '00:00'): ?>
                                    <small class="text-muted d-block"><?= date('H:i', $waktu); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_barang'] ?? '(barang dihapus)'); ?></td>
                            <td>
                                <span class="badge <?= $jenis_row === 'Masuk' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?= htmlspecialchars($jenis_row); ?>
                                </span>
                            </td>
                            <td><?= $row['jumlah'] . ' ' . ($row['satuan'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['keterangan'] ?? ''); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($arsip) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3"><?= $f_bulan !== '' ? 'Tidak ada arsip pada bulan ini.' : 'Belum ada data arsip untuk akun Anda.'; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
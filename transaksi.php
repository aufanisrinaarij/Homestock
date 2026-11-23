<?php
session_start();
include 'koneksi.php';
include 'header.php';

$id_user = $_SESSION['id_user'];

// Deteksi otomatis nama kolom jenis transaksi di database (jenis / jenis_transaksi)
$cek_kolom   = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi_stok LIKE 'jenis_transaksi'");
$kolom_jenis = (mysqli_num_rows($cek_kolom) > 0) ? 'jenis_transaksi' : 'jenis';

// Ambil daftar barang HANYA milik user ini untuk dropdown pilihan
$barang_list = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_user = '$id_user' ORDER BY nama_barang ASC");

// Proses Tambah Transaksi
if (isset($_POST['transaksi'])) {
    $id_barang = intval($_POST['id_barang']);
    $jenis     = ($_POST['jenis'] === 'Keluar') ? 'Keluar' : 'Masuk'; // Masuk / Keluar
    $jumlah    = floatval($_POST['jumlah']);
    $tanggal   = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $keterangan= mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Validasi kepemilikan barang
    $cek_brg = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = '$id_barang' AND id_user = '$id_user'");
    if (mysqli_num_rows($cek_brg) > 0) {
        $data_brg = mysqli_fetch_assoc($cek_brg);
        
        // Hitung perubahan stok
        if ($jenis == 'Masuk') {
            $stok_baru = $data_brg['stok'] + $jumlah;
        } else {
            $stok_baru = $data_brg['stok'] - $jumlah;
            if ($stok_baru < 0) $stok_baru = 0;
        }

        // Simpan transaksi dengan menyertakan id_user
        $simpan = mysqli_query($koneksi, "INSERT INTO transaksi_stok (id_user, id_barang, $kolom_jenis, jumlah, tanggal, keterangan) VALUES ('$id_user', '$id_barang', '$jenis', '$jumlah', '$tanggal', '$keterangan')");

        if ($simpan) {
            // Update stok barang
            mysqli_query($koneksi, "UPDATE barang SET stok = '$stok_baru' WHERE id_barang = '$id_barang' AND id_user = '$id_user'");
            echo "<script>alert('Transaksi berhasil dicatat!'); window.location='transaksi.php';</script>";
        } else {
            $galat = addslashes(mysqli_error($koneksi));
            echo "<script>alert('Transaksi gagal disimpan: $galat');</script>";
        }
    }
}

// ---- Pencarian & Filter riwayat ----
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$f_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$f_dari  = isset($_GET['dari']) ? $_GET['dari'] : '';
$f_sampai= isset($_GET['sampai']) ? $_GET['sampai'] : '';

$where = "WHERE t.id_user = '$id_user'";
if ($q !== '') {
    $q_esc = mysqli_real_escape_string($koneksi, $q);
    $where .= " AND (b.nama_barang LIKE '%$q_esc%' OR t.keterangan LIKE '%$q_esc%')";
}
if ($f_jenis === 'Masuk' || $f_jenis === 'Keluar') {
    $where .= " AND t.$kolom_jenis = '$f_jenis'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_dari)) {
    $where .= " AND DATE(t.tanggal) >= '$f_dari'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f_sampai)) {
    $where .= " AND DATE(t.tanggal) <= '$f_sampai'";
}
$filter_aktif = ($q !== '' || $f_jenis !== '' || $f_dari !== '' || $f_sampai !== '');

// Ambil riwayat transaksi HANYA milik user ini
$riwayat = mysqli_query($koneksi, "SELECT t.*, b.nama_barang, b.satuan FROM transaksi_stok t JOIN barang b ON t.id_barang = b.id_barang $where ORDER BY t.tanggal DESC, t.id_transaksi DESC");

// Parameter filter untuk tombol cetak PDF (ikut filter yang sedang aktif)
$qs_cetak = http_build_query(['q' => $q, 'jenis' => $f_jenis, 'dari' => $f_dari, 'sampai' => $f_sampai]);
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-arrow-left-right"></i> Transaksi Stok Barang</h3>
        <p class="text-muted small">Catat barang masuk atau keluar untuk persediaan Anda.</p>
    </div>
</div>

<div class="row">
    <!-- Form Input Transaksi -->
    <div class="col-md-4 mb-4">
        <div class="card p-3 shadow-sm border-0">
            <h5 class="fw-bold mb-3 text-primary">Input Transaksi Baru</h5>
            <form method="POST">
                <div class="mb-2">
                    <label class="form-label small text-muted">Pilih Barang</label>
                    <select name="id_barang" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php while($b = mysqli_fetch_assoc($barang_list)): ?>
                            <option value="<?= $b['id_barang']; ?>"><?= htmlspecialchars($b['nama_barang']); ?> (Stok: <?= $b['stok'].' '.$b['satuan']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Jenis Transaksi</label>
                    <select name="jenis" class="form-select form-select-sm" required>
                        <option value="Masuk">Barang Masuk (Tambah Stok)</option>
                        <option value="Keluar">Barang Keluar (Kurangi Stok)</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Jumlah</label>
                    <input type="number" step="any" name="jumlah" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Keterangan</label>
                    <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Contoh: Beli di toko / Pakai masak"></textarea>
                </div>
                <button type="submit" name="transaksi" class="btn btn-primary btn-sm w-100">Proses Transaksi</button>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Transaksi -->
    <div class="col-md-8">
        <div class="card p-3 shadow-sm border-0">
            <!-- Pencarian & Filter -->
            <form method="GET" class="row g-2 mb-2">
                <div class="col-md-6">
                    <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" class="form-control form-control-sm" placeholder="Cari nama barang / keterangan...">
                </div>
                <div class="col-md-3">
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        <option value="Masuk" <?= $f_jenis === 'Masuk' ? 'selected' : ''; ?>>Masuk</option>
                        <option value="Keluar" <?= $f_jenis === 'Keluar' ? 'selected' : ''; ?>>Keluar</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> Cari</button>
                    <?php if ($filter_aktif): ?>
                        <a href="transaksi.php" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Dari tanggal</label>
                    <input type="date" name="dari" value="<?= htmlspecialchars($f_dari); ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Sampai tanggal</label>
                    <input type="date" name="sampai" value="<?= htmlspecialchars($f_sampai); ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-md-end">
                    <a href="cetak_laporan.php?<?= $qs_cetak; ?>" target="_blank" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Cetak PDF<?= $filter_aktif ? ' (sesuai filter)' : ''; ?>
                    </a>
                </div>
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
                        <?php $no = 1; while($t = mysqli_fetch_assoc($riwayat)): ?>
                        <?php
                            $jenis_row = $t[$kolom_jenis] ?? '';
                            $waktu     = strtotime($t['tanggal']);
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="text-nowrap">
                                <?= date('d/m/Y', $waktu); ?>
                                <?php if (date('H:i', $waktu) !== '00:00'): ?>
                                    <small class="text-muted d-block"><?= date('H:i', $waktu); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($t['nama_barang']); ?></td>
                            <td>
                                <span class="badge <?= $jenis_row === 'Masuk' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?= htmlspecialchars($jenis_row); ?>
                                </span>
                            </td>
                            <td><?= $t['jumlah'] . ' ' . $t['satuan']; ?></td>
                            <td><?= htmlspecialchars($t['keterangan']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($riwayat) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3"><?= $filter_aktif ? 'Tidak ada transaksi yang cocok dengan pencarian/filter.' : 'Belum ada riwayat transaksi.'; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
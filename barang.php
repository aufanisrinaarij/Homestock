<?php
session_start();
include 'koneksi.php';
include 'header.php';

$id_user = $_SESSION['id_user'];
$pesan = '';

// Proses Tambah Barang
if (isset($_POST['tambah'])) {
    $nama_barang   = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori      = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $satuan        = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $stok          = floatval($_POST['stok']);
    $stok_minimum  = floatval($_POST['stok_minimum']);

    $query = "INSERT INTO barang (id_user, nama_barang, kategori, satuan, stok, stok_minimum) 
              VALUES ('$id_user', '$nama_barang', '$kategori', '$satuan', '$stok', '$stok_minimum')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Barang berhasil ditambahkan!'); window.location='barang.php';</script>";
    }
}

// Proses Hapus Barang (Pastikan miliknya sendiri yang dihapus)
if (isset($_GET['hapus'])) {
    $id_barang = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = '$id_barang' AND id_user = '$id_user'");
    echo "<script>alert('Barang berhasil dihapus!'); window.location='barang.php';</script>";
}

// ---- Pencarian & Filter ----
$q        = isset($_GET['q']) ? trim($_GET['q']) : '';
$f_kat    = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$f_status = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE id_user = '$id_user'";
if ($q !== '') {
    $q_esc = mysqli_real_escape_string($koneksi, $q);
    $where .= " AND (nama_barang LIKE '%$q_esc%' OR kategori LIKE '%$q_esc%')";
}
if ($f_kat !== '') {
    $kat_esc = mysqli_real_escape_string($koneksi, $f_kat);
    $where .= " AND kategori = '$kat_esc'";
}
if ($f_status === 'menipis') {
    $where .= " AND stok <= stok_minimum";
} elseif ($f_status === 'aman') {
    $where .= " AND stok > stok_minimum";
}
$filter_aktif = ($q !== '' || $f_kat !== '' || $f_status !== '');

// Daftar kategori milik user untuk dropdown filter
$kategori_list = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM barang WHERE id_user = '$id_user' ORDER BY kategori ASC");

// Ambil data barang HANYA milik pengguna yang sedang login
$result = mysqli_query($koneksi, "SELECT * FROM barang $where ORDER BY id_barang DESC");
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-box-seam"></i> Data Barang Kebutuhan Rumah Tangga</h3>
        <p class="text-muted small">Kelola daftar barang persediaan khusus akun Anda.</p>
    </div>
</div>

<div class="row">
    <!-- Form Tambah Barang -->
    <div class="col-md-4 mb-4">
        <div class="card p-3 shadow-sm border-0">
            <h5 class="fw-bold mb-3 text-primary">Tambah Barang Baru</h5>
            <form method="POST">
                <div class="mb-2">
                    <label class="form-label small text-muted">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Kategori</label>
                    <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Contoh: Sembako" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Satuan</label>
                    <input type="text" name="satuan" class="form-control form-control-sm" placeholder="Contoh: Kg, Liter, Pcs" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Stok Awal / Saat Ini</label>
                    <input type="number" step="any" name="stok" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Batas Stok Minimum</label>
                    <input type="number" step="any" name="stok_minimum" class="form-control form-control-sm" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary btn-sm w-100">Simpan Data</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Barang -->
    <div class="col-md-8">
        <div class="card p-3 shadow-sm border-0">
            <!-- Pencarian & Filter -->
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" class="form-control form-control-sm" placeholder="Cari nama barang / kategori...">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        <?php while($k = mysqli_fetch_assoc($kategori_list)): ?>
                            <option value="<?= htmlspecialchars($k['kategori']); ?>" <?= $f_kat === $k['kategori'] ? 'selected' : ''; ?>><?= htmlspecialchars($k['kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status Stok</option>
                        <option value="menipis" <?= $f_status === 'menipis' ? 'selected' : ''; ?>>Stok Menipis</option>
                        <option value="aman" <?= $f_status === 'aman' ? 'selected' : ''; ?>>Stok Aman</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i></button>
                    <?php if ($filter_aktif): ?>
                        <a href="barang.php" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead style="background-color: #E3F2FD;">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Min. Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($row['kategori']); ?></td>
                            <td><?= $row['stok'] . ' ' . $row['satuan']; ?></td>
                            <td><?= $row['stok_minimum'] . ' ' . $row['satuan']; ?></td>
                            <td>
                                <a href="edit_barang.php?id=<?= $row['id_barang']; ?>" class="btn btn-warning btn-sm py-0 px-2 text-white"><i class="bi bi-pencil"></i></a>
                                <a href="barang.php?hapus=<?= $row['id_barang']; ?>" class="btn btn-danger btn-sm py-0 px-2" onclick="return confirm('Yakin ingin menghapus barang ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3"><?= $filter_aktif ? 'Tidak ada barang yang cocok dengan pencarian/filter.' : 'Belum ada data barang. Silakan tambah melalui form di samping.'; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
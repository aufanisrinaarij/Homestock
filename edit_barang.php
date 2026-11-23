<?php
session_start();
include 'koneksi.php';

// Wajib login (dicek di sini karena redirect harus sebelum ada output HTML)
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$id_barang = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error     = '';

// Ambil data barang - HANYA milik user yang sedang login
$cek = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = '$id_barang' AND id_user = '$id_user'");
if (mysqli_num_rows($cek) === 0) {
    header("Location: barang.php");
    exit;
}
$barang = mysqli_fetch_assoc($cek);

// Proses simpan perubahan
if (isset($_POST['update'])) {
    $nama_barang  = mysqli_real_escape_string($koneksi, trim($_POST['nama_barang']));
    $kategori     = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    $satuan       = mysqli_real_escape_string($koneksi, trim($_POST['satuan']));
    $stok         = floatval($_POST['stok']);
    $stok_minimum = floatval($_POST['stok_minimum']);

    $update = mysqli_query($koneksi, "UPDATE barang SET
                nama_barang  = '$nama_barang',
                kategori     = '$kategori',
                satuan       = '$satuan',
                stok         = '$stok',
                stok_minimum = '$stok_minimum'
              WHERE id_barang = '$id_barang' AND id_user = '$id_user'");

    if ($update) {
        header("Location: barang.php?edit=sukses");
        exit;
    } else {
        $error = "Gagal menyimpan perubahan: " . mysqli_error($koneksi);
        // Tampilkan kembali isian terakhir pengguna
        $barang['nama_barang']  = $_POST['nama_barang'];
        $barang['kategori']     = $_POST['kategori'];
        $barang['satuan']       = $_POST['satuan'];
        $barang['stok']         = $_POST['stok'];
        $barang['stok_minimum'] = $_POST['stok_minimum'];
    }
}

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-pencil-square"></i> Edit Data Barang</h3>
        <p class="text-muted small">Ubah informasi barang persediaan Anda.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-8">
        <div class="card p-3 shadow-sm border-0">
            <h5 class="fw-bold mb-3 text-primary">Form Edit Barang</h5>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-2">
                    <label class="form-label small text-muted">Nama Barang</label>
                    <input type="text" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Kategori</label>
                    <input type="text" name="kategori" value="<?= htmlspecialchars($barang['kategori']); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Satuan</label>
                    <input type="text" name="satuan" value="<?= htmlspecialchars($barang['satuan']); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Stok Saat Ini</label>
                    <input type="number" step="any" name="stok" value="<?= htmlspecialchars($barang['stok']); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Batas Stok Minimum</label>
                    <input type="number" step="any" name="stok_minimum" value="<?= htmlspecialchars($barang['stok_minimum']); ?>" class="form-control form-control-sm" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="update" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-check-lg"></i> Simpan Perubahan</button>
                    <a href="barang.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php
session_start();
include 'koneksi.php';
include 'header.php';

$id_user = $_SESSION['id_user'];

// Ambil barang yang stoknya menipis HANYA milik pengguna yang sedang login
$query = "SELECT * FROM barang WHERE id_user = '$id_user' AND stok <= stok_minimum ORDER BY nama_barang ASC";
$result = mysqli_query($koneksi, $query);
?>

<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-cart-check"></i> Daftar Belanja (Stok Menipis)</h3>
        <p class="text-muted small">Barang persediaan akun Anda yang sudah mendekati habis dan perlu segera dibeli.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead style="background-color: #E3F2FD;">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($row['kategori']); ?></td>
                            <td class="text-danger fw-bold"><?= $row['stok'] . ' ' . $row['satuan']; ?></td>
                            <td><?= $row['stok_minimum'] . ' ' . $row['satuan']; ?></td>
                            <td>
                                <span class="badge bg-danger">Segera Beli</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Aman! Belum ada barang persediaan Anda yang menipis.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
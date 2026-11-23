<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_POST['tambah_user'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");
        header("Location: pengguna.php");
        exit;
    }
}

if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    if ($id_hapus !== (int) $_SESSION['id_user']) {
        mysqli_query($koneksi, "DELETE FROM users WHERE id_user = $id_hapus");
    }
    header("Location: pengguna.php");
    exit;
}

include 'header.php';
$users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id_user ASC");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h3 class="fw-bold" style="color: #0D47A1;"><i class="bi bi-person-gear"></i> Manajemen Pengguna Sistem</h3>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-3">
            <h5 class="fw-bold mb-3">Tambah Pengguna Baru</h5>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2 small"><?= $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-2">
                    <label class="form-label small">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Username</label>
                    <input type="text" name="username" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Password</label>
                    <input type="password" name="password" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Hak Akses (Role)</label>
                    <select name="role" class="form-select form-select-sm" required>
                        <option value="user">User (Pengguna Biasa)</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="tambah_user" class="btn btn-primary btn-sm w-100">Simpan Pengguna</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead style="background-color: #E3F2FD;">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td>
                                <span class="badge <?= $row['role'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?= strtoupper($row['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['id_user'] != $_SESSION['id_user']): ?>
                                    <a href="pengguna.php?hapus=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm py-0 px-2" onclick="return confirm('Yakin ingin menghapus pengguna ini?')"><i class="bi bi-trash"></i> Hapus</a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sedang Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
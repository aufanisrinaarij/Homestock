<?php
/**
 * Arsip otomatis transaksi bulanan.
 *
 * Semua transaksi di tabel transaksi_stok yang tanggalnya SEBELUM awal bulan ini
 * dipindahkan ke tabel arsip_transaksi, lalu dihapus dari transaksi_stok.
 * Stok barang tidak berubah (sudah dihitung saat transaksi dicatat).
 *
 * Dipanggil dari header.php, jadi berjalan otomatis setiap halaman dibuka
 * (hanya bekerja jika memang ada transaksi bulan lalu).
 */
function arsipkan_transaksi_lama($koneksi)
{
    $batas = date('Y-m-01'); // tanggal 1 bulan ini

    try {
        // 1. Ada transaksi dari bulan-bulan sebelumnya? Kalau tidak, selesai.
        $cek = mysqli_query($koneksi, "SELECT 1 FROM transaksi_stok WHERE DATE(tanggal) < '$batas' LIMIT 1");
        if (!$cek || mysqli_num_rows($cek) === 0) {
            return true;
        }

        // 2. Baca kolom tabel sumber
        $kolom_sumber = [];
        $r = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi_stok");
        while ($c = mysqli_fetch_assoc($r)) {
            $kolom_sumber[] = $c['Field'];
        }

        // 3. Cocokkan dengan kolom tabel arsip (nama sama, atau jenis <-> jenis_transaksi)
        $peta = []; // nama kolom arsip => ekspresi dari transaksi_stok
        $r = mysqli_query($koneksi, "SHOW COLUMNS FROM arsip_transaksi");
        while ($c = mysqli_fetch_assoc($r)) {
            $nama = $c['Field'];
            if (stripos($c['Extra'], 'auto_increment') !== false) {
                continue; // id arsip dibuat otomatis oleh database
            }
            if (in_array($nama, $kolom_sumber, true)) {
                $peta[$nama] = "t.`$nama`";
            } elseif ($nama === 'jenis' && in_array('jenis_transaksi', $kolom_sumber, true)) {
                $peta[$nama] = "t.`jenis_transaksi`";
            } elseif ($nama === 'jenis_transaksi' && in_array('jenis', $kolom_sumber, true)) {
                $peta[$nama] = "t.`jenis`";
            }
        }

        // Kolom wajib harus ada, kalau tidak jangan memindahkan apa pun
        foreach (['id_user', 'id_barang', 'tanggal'] as $wajib) {
            if (!isset($peta[$wajib])) {
                error_log("Arsip otomatis dibatalkan: kolom '$wajib' tidak ada di arsip_transaksi.");
                return false;
            }
        }

        $daftar_tujuan = '`' . implode('`, `', array_keys($peta)) . '`';
        $daftar_sumber = implode(', ', $peta);

        // 4. Salin lalu hapus, dalam satu transaksi database
        mysqli_begin_transaction($koneksi);

        mysqli_query($koneksi, "INSERT INTO arsip_transaksi ($daftar_tujuan)
                                SELECT $daftar_sumber FROM transaksi_stok t
                                WHERE DATE(t.tanggal) < '$batas'");
        $disalin = mysqli_affected_rows($koneksi);

        mysqli_query($koneksi, "DELETE FROM transaksi_stok WHERE DATE(tanggal) < '$batas'");
        $dihapus = mysqli_affected_rows($koneksi);

        if ($disalin !== $dihapus) {
            mysqli_rollback($koneksi); // jumlah tidak cocok -> batalkan semuanya
            return false;
        }

        mysqli_commit($koneksi);
        return true;

    } catch (Throwable $e) {
        try { mysqli_rollback($koneksi); } catch (Throwable $e2) {}
        error_log('Arsip otomatis gagal: ' . $e->getMessage());
        return false;
    }
}
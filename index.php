
<?php
include "koneksi.php";

function aman($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

$pesan = "";
$edit = null;

// PROSES SIMPAN DAN EDIT
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama_produk']);
    $kategori = (int) $_POST['id_kategori'];
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    $id = (int) ($_POST['id_produk'] ?? 0);

    if ($nama == "" || $kategori <= 0 || $harga < 0 || $stok < 0) {
        $pesan = "Data belum lengkap atau tidak valid!";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare(
                "UPDATE produk SET id_kategori=?, nama_produk=?, harga=?, stok=? WHERE id_produk=?"
            );
            $stmt->bind_param("isdii", $kategori, $nama, $harga, $stok, $id);
            $berhasil = $stmt->execute();
            $pesan = $berhasil ? "Data berhasil diedit!" : "Gagal mengedit data!";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO produk (id_kategori, nama_produk, harga, stok) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("isdi", $kategori, $nama, $harga, $stok);
            $berhasil = $stmt->execute();
            $pesan = $berhasil ? "Data berhasil disimpan!" : "Gagal menyimpan data!";
        }
    }
}

// PROSES HAPUS
if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id_produk'];

    $stmt = $conn->prepare(
        "DELETE FROM produk WHERE id_produk=?"
    );
    $stmt->bind_param("i", $id);

    $pesan = $stmt->execute()
        ? "Data berhasil dihapus!"
        : "Data gagal dihapus!";
}

// AMBIL DATA UNTUK EDIT
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = $conn->prepare(
        "SELECT * FROM produk WHERE id_produk=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $hasilEdit = $stmt->get_result();
    $edit = $hasilEdit->fetch_assoc();
}

// AMBIL DATA KATEGORI
$kategori = $conn->query(
    "SELECT * FROM kategori ORDER BY nama_kategori"
);

// AMBIL DATA PRODUK DAN KATEGORI
$produk = $conn->query(
    "SELECT produk.*, kategori.nama_kategori
     FROM produk
     INNER JOIN kategori
     ON produk.id_kategori = kategori.id_kategori
     ORDER BY produk.id_produk DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>DATA PRODUK</h1>
    <p>
    <a href="index.php">Data Produk</a> |
    <a href="jenis_pelanggan.php">Jenis Pelanggan</a> |
    <a href="pelanggan.php">Data Pelanggan</a>
</p>
    <p class="judul">Praktikum Pemrograman Web I - Pertemuan 9</p>

    <?php if ($pesan != ""): ?>
        <div class="pesan">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="form-box">

        <h2><?= $edit ? "Edit Data Produk" : "Tambah Data Produk" ?></h2>

        <form method="POST">

            <input type="hidden" name="id_produk"
                value="<?= aman($edit['id_produk'] ?? '') ?>">

            <label>Nama Produk</label>
            <input type="text" name="nama_produk"
                value="<?= aman($edit['nama_produk'] ?? '') ?>"
                placeholder="Masukkan nama produk" required>

            <label>Kategori</label>
            <select name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>

                <?php while ($k = $kategori->fetch_assoc()): ?>
                    <option value="<?= $k['id_kategori'] ?>"
                        <?= isset($edit['id_kategori']) &&
                        $edit['id_kategori'] == $k['id_kategori']
                        ? 'selected' : '' ?>>
                        <?= aman($k['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Harga</label>
            <input type="number" name="harga" min="0" step="0.01"
                value="<?= aman($edit['harga'] ?? '') ?>"
                placeholder="Masukkan harga" required>

            <label>Stok</label>
            <input type="number" name="stok" min="0"
                value="<?= aman($edit['stok'] ?? '') ?>"
                placeholder="Masukkan stok" required>

            <button type="submit" name="simpan" class="btn-simpan">
                <?= $edit ? "Simpan Edit" : "Simpan" ?>
            </button>

            <?php if ($edit): ?>
                <a href="index.php" class="btn-batal">Batal</a>
            <?php endif; ?>

        </form>
    </div>

    <div class="table-box">

        <h2>Daftar Produk</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($row = $produk->fetch_assoc()): ?>

                <tr>
                    <td><?= $row['id_produk'] ?></td>

                    <td><?= aman($row['nama_produk']) ?></td>

                    <td><?= aman($row['nama_kategori']) ?></td>

                    <td>
                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                    </td>

                    <td><?= $row['stok'] ?></td>

                    <td class="aksi">

                        <a href="index.php?edit=<?= $row['id_produk'] ?>"
                            class="btn-edit">Edit</a>

                        <form method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus produk ini?')">

                            <input type="hidden" name="id_produk"
                                value="<?= $row['id_produk'] ?>">

                            <button type="submit" name="hapus"
                                class="btn-hapus">
                                Hapus
                            </button>

                        </form>

                    </td>
                </tr>

                <?php endwhile; ?>

            </tbody>
        </table>

    </div>

    <footer>
        Praktikum Pemrograman Web I | Database Relasional dan SQL CRUD
    </footer>

</div>

</body>
</html>

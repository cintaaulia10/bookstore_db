<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/koneksi.php';

// Tambah buku
if(isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $harga = (int)preg_replace('/[^0-9]/', '', $_POST['harga']); // Hanya ambil angka
    $id_kategori = (int)$_POST['id_kategori'];
    $stok = (int)$_POST['stok'];

    // Handle file upload
    $gambar = '';
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/css/uploads/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $original_name = $_FILES['gambar']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $clean_name = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
        $new_filename = $clean_name . '.' . $file_extension;
        
        if(file_exists($target_dir . $new_filename)) {
            $new_filename = time() . '_' . $clean_name . '.' . $file_extension;
        }
        
        $target_file = $target_dir . $new_filename;
        if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar = $new_filename;
        }
    }

    $query = "INSERT INTO buku (judul, penulis, harga, id_kategori, stok, gambar) 
              VALUES ('$judul', '$penulis', '$harga', '$id_kategori', '$stok', '$gambar')";
    
    if($koneksi->query($query)) {
        echo "<script>alert('Buku berhasil ditambahkan!'); window.location.href='kelola_buku.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan buku: " . $koneksi->error . "');</script>";
    }
}

// Edit buku
if(isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id = (int)$_POST['id'];
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $harga = (int)preg_replace('/[^0-9]/', '', $_POST['harga']); // Hanya ambil angka
    $id_kategori = (int)$_POST['id_kategori'];
    $stok = (int)$_POST['stok'];

    $gambar_update = '';
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/css/uploads/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $original_name = $_FILES['gambar']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $old = $koneksi->query("SELECT gambar FROM buku WHERE id = $id")->fetch_assoc();
            if($old['gambar'] && file_exists("../assets/css/uploads/" . $old['gambar'])) {
                unlink("../assets/css/uploads/" . $old['gambar']);
            }
            $gambar_update = ", gambar = '$new_filename'";
        }
    }

    $query = "UPDATE buku SET judul = '$judul', penulis = '$penulis', harga = '$harga',
              id_kategori = '$id_kategori', stok = '$stok' $gambar_update
              WHERE id = $id";
    
    if($koneksi->query($query)) {
        echo "<script>alert('Buku berhasil diupdate!'); window.location.href='kelola_buku.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate buku: " . $koneksi->error . "');</script>";
    }
}

// Hapus buku
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $book = $koneksi->query("SELECT gambar FROM buku WHERE id = $id")->fetch_assoc();
    if($book && !empty($book['gambar']) && file_exists("../assets/css/uploads/" . $book['gambar'])) {
        unlink("../assets/css/uploads/" . $book['gambar']);
    }
    $koneksi->query("DELETE FROM buku WHERE id = $id");
    header("Location: kelola_buku.php");
    exit();
}

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
if($search) {
    $buku = $koneksi->query("SELECT b.*, k.nama_kategori FROM buku b 
                             LEFT JOIN kategori k ON b.id_kategori = k.id 
                             WHERE b.judul LIKE '%$search%' OR b.penulis LIKE '%$search%' 
                             ORDER BY b.id DESC");
} else {
    $buku = $koneksi->query("SELECT b.*, k.nama_kategori FROM buku b 
                             LEFT JOIN kategori k ON b.id_kategori = k.id 
                             ORDER BY b.id DESC");
}

$kategori = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori");
$nama_admin = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - BookStore Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fb; color: #1e293b; }
        
        .header { background: #0f172a; padding: 16px 0; position: sticky; top: 0; z-index: 100; }
        .container { max-width: 1280px; margin: auto; padding: 0 40px; }
        .header-content { display: flex; align-items: center; gap: 20px; }
        .logo { display: flex; align-items: center; gap: 10px; color: white; }
        .logo .logo-circle {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 35%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-circle img { width: 35px; height: 35px; object-fit: contain; }
        .logo h2 { font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; color: white; }
        
        .search-wrapper { flex: 1; max-width: 350px; margin-left: auto; }
        .search-wrapper form { display: flex; position: relative; }
        .search-wrapper input { 
            width: 100%; padding: 12px 18px 12px 46px; border-radius: 999px; 
            border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.08); 
            color: white; font-size: 14px; 
        }
        .search-wrapper input::placeholder { color: rgba(255,255,255,0.6); }
        .search-wrapper input:focus {
            outline: none; border-color: #3498db; background: rgba(255,255,255,0.12);
        }
        .search-wrapper button { 
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%); 
            border: none; background: none; color: white; cursor: pointer; 
        }
        
        .admin-info {
            display: flex; align-items: center; gap: 20px; color: white; margin-left: 15px;
        }
        .admin-name { font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .logout-link {
            color: #f87171; text-decoration: none; font-size: 14px; padding: 6px 12px;
            border-radius: 8px; transition: background 0.3s;
        }
        .logout-link:hover { background: rgba(248, 113, 113, 0.1); }
        
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { 
            width: 220px; 
            background: white; 
            padding: 30px 0; 
            position: sticky; 
            top: 77px; 
            height: calc(100vh - 77px);
            overflow-y: auto;
            border-right: 1px solid #e5e7eb;
        }
        .sidebar a { 
            display: block; 
            padding: 12px 24px; 
            text-decoration: none; 
            color: #334155; 
            margin-bottom: 4px; 
            font-weight: 500; 
            transition: all 0.3s ease; 
            font-size: 14px;
        }
        .sidebar a:hover { 
            background: #f1f5f9; 
            color: #0f172a; 
        }
        .sidebar a.active { 
            background: #f1f5f9; 
            color: #0f172a;
            border-left: 3px solid #3498db;
        }
        
        .content { flex: 1; padding: 30px 40px; }
        .content h2 { font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .content > p { color: #64748b; margin-bottom: 30px; }
        
        .form-section { background: white; padding: 24px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .form-section h3 { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e5e7eb; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 500; color: #374151; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select { 
            width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; 
            font-size: 14px; transition: 0.3s; background: #f9fafb;
        }
        .form-group input:focus, .form-group select:focus { 
            outline: none; border-color: #3498db; background: white; box-shadow: 0 0 0 3px rgba(52,152,219,0.1); 
        }
        
        .btn { padding: 12px 28px; border: none; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: 0.3s; font-size: 14px; }
        .btn-primary { background: #0f172a; color: white; }
        .btn-primary:hover { background: #1e293b; transform: translateY(-1px); }
        .btn-success { background: #27ae60; color: white; }
        .btn-success:hover { background: #229954; transform: translateY(-1px); }
        
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8fafc; padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .table td { padding: 16px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .table tr:hover { background: #f9fafb; }
        .book-cover-thumb { width: 60px; height: 80px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .action-buttons { display: flex; gap: 8px; }
        .icon-btn { display: inline-flex; justify-content: center; align-items: center; width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; transition: all 0.2s ease; }
        .icon-edit { background: #eef2ff; color: #4338ca; }
        .icon-delete { background: #fee2e7; color: #b91c1c; }
        .icon-btn:hover { transform: translateY(-2px); filter: brightness(0.95); }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 20px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; }
        .modal-content h3 { font-family: 'Poppins', sans-serif; font-size: 22px; margin-bottom: 20px; }
        .close { float: right; font-size: 28px; cursor: pointer; color: #64748b; }
        .close:hover { color: #1e293b; }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 768px) { 
            .container { padding: 0 20px; } 
            .wrapper { flex-direction: column; } 
            .sidebar { width: 100%; height: auto; position: static; } 
            .content { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container header-content">
        <div class="logo">
            <div class="logo-circle">
                <img src="../assets/css/images/logo_buku.png" alt="Logo">
            </div>
            <h2>BookStore Admin</h2>
        </div>

        <div class="search-wrapper">
            <form method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <div class="admin-info">
            <div class="admin-name">
                <i class="fas fa-user-shield"></i> 
                <?= htmlspecialchars($nama_admin) ?>
            </div>
            <a href="../logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="wrapper">
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_buku.php" class="active">Kelola Buku</a>
        <a href="kelola_kategori.php">Kategori</a>
        <a href="kelola_user.php">User</a>
        <a href="kelola_pesanan.php">Pesanan</a>
    </div>

    <div class="content">
        <h2>Kelola Buku</h2>
        <p>Kelola data buku yang tersedia di toko Anda.</p>

        <!-- FORM TAMBAH BUKU -->
        <div class="form-section">
            <h3>Tambah Buku Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="tambah">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Judul Buku</label>
                        <input type="text" name="judul" required placeholder="Masukkan judul buku">
                    </div>
                    <div class="form-group">
                        <label>Penulis</label>
                        <input type="text" name="penulis" placeholder="Masukkan nama penulis">
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="text" name="harga" required placeholder="Contoh: 50000 atau 125.000" 
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" required placeholder="Jumlah stok buku" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php 
                            $kategori_all = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori");
                            while($k = $kategori_all->fetch_assoc()): 
                            ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gambar Cover</label>
                        <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg">
                        <small style="color:#64748b;">Format: JPG, PNG. Maksimal 2MB</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    Simpan Buku
                </button>
            </form>
        </div>

        <!-- TABEL BUKU -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($buku && $buku->num_rows > 0): ?>
                        <?php while($row = $buku->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if(!empty($row['gambar']) && file_exists("../assets/css/uploads/" . $row['gambar'])): ?>
                                    <img src="../assets/css/uploads/<?= $row['gambar'] ?>" alt="Cover" class="book-cover-thumb">
                                <?php else: ?>
                                    <div style="width: 60px; height: 80px; background: #e5e7eb; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-book" style="color: #9ca3af; font-size: 24px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                            <td><?= htmlspecialchars($row['penulis'] ?: '-') ?></td>
                            <td><strong style="color: #e74c3c;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></strong></td>
                            <td>
                                <span style="background: #d1fae5; color: #059669; padding: 4px 10px; border-radius: 20px; font-size: 12px;">
                                    <i class="fas fa-boxes"></i> <?= number_format($row['stok']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 20px; font-size: 12px;">
                                    <?= htmlspecialchars($row['nama_kategori'] ?: 'Uncategorized') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="icon-btn icon-edit" onclick="editBook(
                                        <?= $row['id'] ?>, 
                                        '<?= htmlspecialchars(addslashes($row['judul'])) ?>', 
                                        '<?= htmlspecialchars(addslashes($row['penulis'])) ?>', 
                                        <?= $row['harga'] ?>, 
                                        <?= $row['id_kategori'] ?>,
                                        <?= $row['stok'] ?>
                                    )">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['id'] ?>" class="icon-btn icon-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 60px;">
                                <i class="fas fa-book-open" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
                                Tidak ada data buku
                                <?php if($search): ?>
                                    <br><small>dengan kata kunci "<?= htmlspecialchars($search) ?>"</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Edit Buku</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" id="edit_judul" name="judul" required>
                </div>
                <div class="form-group">
                    <label>Penulis</label>
                    <input type="text" id="edit_penulis" name="penulis">
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="text" id="edit_harga" name="harga" required 
                           onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" id="edit_stok" name="stok" required min="0">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="edit_id_kategori" name="id_kategori" required>
                        <option value="">Pilih Kategori</option>
                        <?php 
                        $kategori_all = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori");
                        while($k = $kategori_all->fetch_assoc()): 
                        ?>
                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gambar Baru</label>
                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/jpg">
                    <small style="color: #64748b;">Biarkan kosong jika tidak ingin mengubah gambar</small>
                </div>
            </div>
            <button type="submit" class="btn btn-success">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<div class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 BookStore | Sistem Manajemen Toko Buku Online</p>
        </div>
    </div>
</div>

<script>
function editBook(id, judul, penulis, harga, id_kategori, stok) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_judul').value = judul;
    document.getElementById('edit_penulis').value = penulis;
    document.getElementById('edit_harga').value = harga;
    document.getElementById('edit_id_kategori').value = id_kategori;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
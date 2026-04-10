<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/koneksi.php';

// Tambah kategori (dengan buku dummy)
if(isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $jumlah_buku = (int)$_POST['jumlah_buku'];
    $total_stok = (int)$_POST['total_stok'];
    
    // Jika jumlah buku > 0, hitung stok per buku (rata-rata)
    $stok_per_buku = ($jumlah_buku > 0) ? floor($total_stok / $jumlah_buku) : 0;
    $sisa_stok = ($jumlah_buku > 0) ? $total_stok % $jumlah_buku : 0;
    
    // Insert kategori
    $query = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    if($koneksi->query($query)) {
        $id_kategori = $koneksi->insert_id;
        
        // Insert buku dummy sesuai jumlah yang diminta
        for($i = 1; $i <= $jumlah_buku; $i++) {
            $stok_buku = $stok_per_buku;
            if($i <= $sisa_stok) $stok_buku++; // Distribusi sisa stok
            
            $judul_buku = "Buku $nama_kategori " . $i;
            $penulis = "Penulis " . $nama_kategori;
            $penerbit = "Penerbit " . $nama_kategori;
            $tahun = date('Y');
            $deskripsi = "Buku dalam kategori $nama_kategori";
            
            $insert_buku = "INSERT INTO buku (judul, penulis, penerbit, tahun, deskripsi, stok, id_kategori) 
                            VALUES ('$judul_buku', '$penulis', '$penerbit', '$tahun', '$deskripsi', '$stok_buku', '$id_kategori')";
            $koneksi->query($insert_buku);
        }
        
        echo "<script>alert('Kategori berhasil ditambahkan dengan $jumlah_buku buku dan total stok $total_stok!'); window.location.href='kelola_kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan kategori: " . $koneksi->error . "');</script>";
    }
}

// Hapus kategori
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Hapus semua buku dalam kategori terlebih dahulu
    $koneksi->query("DELETE FROM buku WHERE id_kategori = $id");
    
    // Hapus kategori
    $koneksi->query("DELETE FROM kategori WHERE id = $id");
    header("Location: kelola_kategori.php");
    exit();
}

// Edit kategori lengkap (semua field)
if(isset($_POST['aksi']) && $_POST['aksi'] == 'edit_lengkap') {
    $id = (int)$_POST['id'];
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $total_buku_baru = (int)$_POST['total_buku'];
    $total_stok_baru = (int)$_POST['total_stok'];
    
    // Update nama kategori
    $koneksi->query("UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id = $id");
    
    // Ambil jumlah buku saat ini
    $current_buku = $koneksi->query("SELECT COUNT(*) as total FROM buku WHERE id_kategori = $id");
    $current_jumlah = $current_buku->fetch_assoc()['total'];
    
    if($total_buku_baru > $current_jumlah) {
        // Tambah buku baru
        $tambah = $total_buku_baru - $current_jumlah;
        $stok_per_buku = ($total_buku_baru > 0) ? floor($total_stok_baru / $total_buku_baru) : 0;
        $sisa_stok = ($total_buku_baru > 0) ? $total_stok_baru % $total_buku_baru : 0;
        
        // Update stok semua buku yang ada
        $koneksi->query("UPDATE buku SET stok = $stok_per_buku WHERE id_kategori = $id");
        
        // Tambah buku baru
        for($i = 1; $i <= $tambah; $i++) {
            $stok_buku = $stok_per_buku;
            if(($current_jumlah + $i) <= $sisa_stok) $stok_buku++;
            
            $judul_buku = "Buku $nama_kategori " . ($current_jumlah + $i);
            $penulis = "Penulis " . $nama_kategori;
            $penerbit = "Penerbit " . $nama_kategori;
            $tahun = date('Y');
            $deskripsi = "Buku dalam kategori $nama_kategori";
            
            $koneksi->query("INSERT INTO buku (judul, penulis, penerbit, tahun, deskripsi, stok, id_kategori) 
                            VALUES ('$judul_buku', '$penulis', '$penerbit', '$tahun', '$deskripsi', '$stok_buku', '$id')");
        }
    } elseif($total_buku_baru < $current_jumlah) {
        // Hapus buku berlebih
        $hapus = $current_jumlah - $total_buku_baru;
        $limit = $hapus;
        $koneksi->query("DELETE FROM buku WHERE id_kategori = $id ORDER BY id DESC LIMIT $limit");
        
        // Update stok semua buku yang tersisa
        $stok_per_buku = ($total_buku_baru > 0) ? floor($total_stok_baru / $total_buku_baru) : 0;
        $koneksi->query("UPDATE buku SET stok = $stok_per_buku WHERE id_kategori = $id");
    } else {
        // Jumlah buku sama, hanya update stok
        $stok_per_buku = ($total_buku_baru > 0) ? floor($total_stok_baru / $total_buku_baru) : 0;
        $koneksi->query("UPDATE buku SET stok = $stok_per_buku WHERE id_kategori = $id");
    }
    
    echo "<script>alert('Data kategori berhasil diupdate!'); window.location.href='kelola_kategori.php';</script>";
}

// Update stok massal
if(isset($_POST['aksi']) && $_POST['aksi'] == 'update_stok') {
    $id_kategori = (int)$_POST['id_kategori'];
    $stok_baru = (int)$_POST['stok_baru'];
    
    // Update semua buku dalam kategori ini
    $koneksi->query("UPDATE buku SET stok = $stok_baru WHERE id_kategori = $id_kategori");
    echo "<script>alert('Stok semua buku dalam kategori berhasil diupdate!'); window.location.href='kelola_kategori.php';</script>";
}

// Urutkan berdasarkan ID ASC (1,2,3,4...)
$kategori = $koneksi->query("SELECT k.*, 
                              (SELECT COUNT(*) FROM buku WHERE id_kategori = k.id) as total_buku,
                              (SELECT SUM(stok) FROM buku WHERE id_kategori = k.id) as total_stok
                              FROM kategori k ORDER BY k.id ASC");
$nama_admin = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - PustakaStore Admin</title>
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
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 500; color: #374151; margin-bottom: 8px; font-size: 14px; }
        .form-group input { 
            width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; 
            font-size: 14px; transition: 0.3s; background: #f9fafb;
        }
        .form-group input:focus { 
            outline: none; border-color: #3498db; background: white; box-shadow: 0 0 0 3px rgba(52,152,219,0.1); 
        }
        
        .btn { padding: 12px 28px; border: none; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: 0.3s; font-size: 14px; }
        .btn-primary { background: #0f172a; color: white; }
        .btn-primary:hover { background: #1e293b; transform: translateY(-1px); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .btn-info { background: #3498db; color: white; }
        .btn-info:hover { background: #2980b9; }
        
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8fafc; padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .table td { padding: 16px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .table tr:hover { background: #f9fafb; }
        
        .stock-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .stock-high {
            background: #d1fae5;
            color: #059669;
        }
        .stock-medium {
            background: #fef3c7;
            color: #d97706;
        }
        .stock-low {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .action-buttons { display: flex; gap: 8px; }
        .icon-btn { display: inline-flex; justify-content: center; align-items: center; width: 36px; height: 36px; border-radius: 10px; border: none; cursor: pointer; transition: all 0.2s ease; }
        .icon-edit { background: #eef2ff; color: #4338ca; }
        .icon-delete { background: #fee2e7; color: #b91c1c; }
        .icon-stock { background: #fef3c7; color: #d97706; }
        .icon-view { background: #d1fae5; color: #059669; }
        .icon-btn:hover { transform: translateY(-2px); filter: brightness(0.95); }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; border-radius: 20px; width: 90%; max-width: 500px; }
        .modal-content h3 { font-family: 'Poppins', sans-serif; font-size: 22px; margin-bottom: 20px; color: #1e293b; }
        .close { float: right; font-size: 28px; cursor: pointer; color: #64748b; }
        .close:hover { color: #1e293b; }
        
        .info-note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 13px;
            color: #92400e;
        }
        
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
            <h2>PustakaStore Admin</h2>
        </div>

        <div class="search-wrapper">
            <form action="kelola_buku.php" method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Cari buku...">
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
        <a href="kelola_buku.php">Kelola Buku</a>
        <a href="kelola_kategori.php" class="active">Kategori</a>
        <a href="kelola_user.php">User</a>
        <a href="kelola_pesanan.php">Pesanan</a>
    </div>

    <div class="content">
        <h2>Kelola Kategori</h2>
        <p>Kelola data kategori buku yang tersedia di toko Anda.</p>

        <!-- FORM TAMBAH KATEGORI (dengan Jumlah Buku dan Total Stok) -->
        <div class="form-section">
            <h3>Tambah Kategori Baru</h3>
            <div class="info-note">
                <i class="fas fa-info-circle"></i> <strong>Informasi:</strong> Jumlah Buku dan Total Stok akan otomatis membuat buku dummy sesuai inputan Anda.
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="form-group">
                    <label>Nama Kategori <span style="color: red;">*</span></label>
                    <input type="text" name="nama_kategori" required placeholder="Masukkan nama kategori (contoh: Novel, Komik, Pelajaran)">
                </div>
                <div class="form-group">
                    <label>Jumlah Buku <span style="color: red;">*</span></label>
                    <input type="number" name="jumlah_buku" id="jumlah_buku" required min="1" value="1" placeholder="Jumlah buku dalam kategori ini">
                </div>
                <div class="form-group">
                    <label>Total Stok <span style="color: red;">*</span></label>
                    <input type="number" name="total_stok" id="total_stok" required min="0" value="10" placeholder="Total stok keseluruhan">
                    <small style="color: #64748b; display: block; margin-top: 5px;">
                        <i class="fas fa-calculator"></i> Stok per buku: <span id="stok_per_buku_preview">10</span>
                    </small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Kategori
                </button>
            </form>
        </div>

        <!-- TABEL KATEGORI -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th>Total Stok</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($kategori && $kategori->num_rows > 0): ?>
                        <?php while($row = $kategori->fetch_assoc()): 
                            $total_stok = $row['total_stok'] ?? 0;
                            if($total_stok > 50) {
                                $stock_class = 'stock-high';
                            } elseif($total_stok > 10) {
                                $stock_class = 'stock-medium';
                            } else {
                                $stock_class = 'stock-low';
                            }
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                            <td>
                                <span style="font-weight: 600; color: #3498db;">
                                    <i class="fas fa-book"></i> <?= number_format($row['total_buku'] ?? 0) ?> buku
                                </span>
                            </td>
                            <td>
                                <span class="stock-badge <?= $stock_class ?>">
                                    <i class="fas fa-boxes"></i> <?= number_format($total_stok) ?> stok
                                </span>
                            </td>
                            <td><?= isset($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-' ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="icon-btn icon-edit" onclick="editKategoriLengkap(
                                        <?= $row['id'] ?>,
                                        '<?= htmlspecialchars(addslashes($row['nama_kategori'])) ?>',
                                        <?= $row['total_buku'] ?? 0 ?>,
                                        <?= $total_stok ?>,
                                        '<?= isset($row['created_at']) ? date('Y-m-d\TH:i', strtotime($row['created_at'])) : date('Y-m-d\TH:i') ?>'
                                    )">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['id'] ?>" class="icon-btn icon-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Semua buku dalam kategori juga akan terhapus.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px;">
                                <i class="fas fa-tags" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
                                Tidak ada data kategori
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT KATEGORI LENGKAP (Semua Field Bisa Diedit) -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Edit Kategori</h3>
        <form method="POST">
            <input type="hidden" name="aksi" value="edit_lengkap">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>ID Kategori</label>
                <input type="text" id="edit_id_display" class="form-control" readonly disabled style="background:#f1f5f9;">
            </div>
            
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" id="edit_nama" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Jumlah Buku</label>
                <input type="number" name="total_buku" id="edit_jumlah" class="form-control" min="0" required>
                <small style="color:#64748b;">*Jika diubah, sistem akan otomatis menambah/menghapus buku</small>
            </div>
            
            <div class="form-group">
                <label>Total Stok</label>
                <input type="number" name="total_stok" id="edit_stok" class="form-control" min="0" required>
                <small style="color:#64748b;">*Stok akan didistribusikan ke semua buku dalam kategori ini</small>
            </div>
            
            <div class="form-group">
                <label>Tanggal Dibuat</label>
                <input type="datetime-local" name="created_at" id="edit_tanggal" class="form-control">
                <small style="color:#64748b;">*Perubahan tanggal hanya untuk tampilan</small>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Data</button>
            <button type="button" class="btn btn-warning" onclick="closeModal()" style="margin-left: 10px;"><i class="fas fa-times"></i> Kembali</button>
        </form>
    </div>
</div>

<div class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 PustakaStore | Toko Buku Online Indonesia</p>
        </div>
    </div>
</div>

<script>
// Preview stok per buku saat mengisi form tambah
const jumlahBukuInput = document.getElementById('jumlah_buku');
const totalStokInput = document.getElementById('total_stok');
const stokPerBukuSpan = document.getElementById('stok_per_buku_preview');

function updateStokPreview() {
    let jumlah = parseInt(jumlahBukuInput.value) || 0;
    let total = parseInt(totalStokInput.value) || 0;
    
    if(jumlah > 0) {
        let perBuku = Math.floor(total / jumlah);
        stokPerBukuSpan.textContent = perBuku + ' (sisa ' + (total % jumlah) + ' buku mendapat +1 stok)';
    } else {
        stokPerBukuSpan.textContent = '0';
    }
}

if(jumlahBukuInput && totalStokInput) {
    jumlahBukuInput.addEventListener('input', updateStokPreview);
    totalStokInput.addEventListener('input', updateStokPreview);
    updateStokPreview();
}

function editKategoriLengkap(id, nama, jumlah_buku, total_stok, created_at) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_id_display').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_jumlah').value = jumlah_buku;
    document.getElementById('edit_stok').value = total_stok;
    document.getElementById('edit_tanggal').value = created_at;
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
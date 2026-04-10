<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/koneksi.php';

// Update status pesanan
if(isset($_POST['update_status'])) {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $koneksi->query("UPDATE pesanan SET status = '$status' WHERE id = $id_pesanan");
    header("Location: kelola_pesanan.php");
    exit();
}

// Hapus pesanan
if(isset($_GET['hapus'])) {
    $id_pesanan = (int)$_GET['hapus'];
    // Hapus detail pesanan terlebih dahulu
    $koneksi->query("DELETE FROM detail_pesanan WHERE id_pesanan = $id_pesanan");
    // Hapus pesanan
    $koneksi->query("DELETE FROM pesanan WHERE id = $id_pesanan");
    header("Location: kelola_pesanan.php");
    exit();
}

// Hitung statistik
$pending_count = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
$lunas_count = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status='lunas'")->fetch_assoc()['total'] ?? 0;
$dikirim_count = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status='dikirim'")->fetch_assoc()['total'] ?? 0;
$selesai_count = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status='selesai'")->fetch_assoc()['total'] ?? 0;

// Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
if($search) {
    $pesanan = $koneksi->query("SELECT p.*, u.username, u.email FROM pesanan p JOIN users u ON p.id_user = u.id WHERE u.username LIKE '%$search%' OR p.id LIKE '%$search%' ORDER BY p.id DESC");
} else {
    $pesanan = $koneksi->query("SELECT p.*, u.username, u.email FROM pesanan p JOIN users u ON p.id_user = u.id ORDER BY p.id DESC");
}

$nama_admin = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - BookStore Admin</title>
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
        .logo-circle img { width: 65px; height: 65px; object-fit: contain; }
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
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid; transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-card.pending { border-left-color: #f39c12; }
        .stat-card.lunas { border-left-color: #3498db; }
        .stat-card.dikirim { border-left-color: #8e44ad; }
        .stat-card.selesai { border-left-color: #27ae60; }
        .stat-card h3 { font-size: 13px; font-weight: 500; color: #64748b; margin-bottom: 8px; }
        .stat-card .number { font-size: 32px; font-weight: 700; color: #1e293b; }
        .stat-card .label { font-size: 12px; color: #64748b; margin-top: 5px; }
        
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8fafc; padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .table td { padding: 16px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .table tr:hover { background: #f9fafb; }
        
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-lunas { background: #dbeafe; color: #2563eb; }
        .status-dikirim { background: #e0e7ff; color: #5b21b6; }
        .status-selesai { background: #d1fae5; color: #065f46; }
        
        .payment-method { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #f3f4f6; border-radius: 20px; font-size: 12px; }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { 
            .container { padding: 0 20px; } 
            .wrapper { flex-direction: column; } 
            .sidebar { width: 100%; height: auto; position: static; } 
            .content { padding: 20px; } 
            .stats-grid { grid-template-columns: 1fr; }
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
            <h2> PustakaStore</h2>
        </div>

        <div class="search-wrapper">
            <form method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Cari pesanan..." value="<?= htmlspecialchars($search) ?>">
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
        <a href="kelola_kategori.php">Kategori</a>
        <a href="kelola_user.php">User</a>
        <a href="kelola_pesanan.php" class="active">Pesanan</a>
    </div>

    <div class="content">
        <h2>List Pesanan</h2>
        <p>Daftar semua pesanan dari pengguna BookStore.</p>

        <!-- STATISTIK -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3>Pending</h3>
                <div class="number"><?= number_format($pending_count) ?></div>
                <div class="label">Menunggu konfirmasi</div>
            </div>
            <div class="stat-card lunas">
                <h3>Lunas</h3>
                <div class="number"><?= number_format($lunas_count) ?></div>
                <div class="label">Sudah dibayar</div>
            </div>
            <div class="stat-card dikirim">
                <h3>Dikirim</h3>
                <div class="number"><?= number_format($dikirim_count) ?></div>
                <div class="label">Sedang dikirim</div>
            </div>
            <div class="stat-card selesai">
                <h3>Selesai</h3>
                <div class="number"><?= number_format($selesai_count) ?></div>
                <div class="label">Pesanan selesai</div>
            </div>
        </div>

        <!-- TABEL PESANAN -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($pesanan && $pesanan->num_rows > 0): ?>
                        <?php while($row = $pesanan->fetch_assoc()): 
                            // Menggunakan id_pesanan (bukan pesanan_id)
                            $items_count = $koneksi->query("SELECT SUM(jumlah) as total FROM detail_pesanan WHERE id_pesanan = {$row['id']}")->fetch_assoc()['total'] ?? 0;
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                <br><small><?= $items_count ?> item</small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['username']) ?></strong>
                                <br><small><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td>
                                <strong style="color: #e74c3c;">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $row['status'] ?>">
                                    <?php 
                                    switch($row['status']){
                                        case 'pending': echo '⏳ Pending'; break;
                                        case 'lunas': echo '💰 Lunas'; break;
                                        case 'dikirim': echo '📦 Dikirim'; break;
                                        case 'selesai': echo '✅ Selesai'; break;
                                        default: echo ucfirst($row['status']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="payment-method">
                                    <?php if($row['metode_pembayaran'] == 'bayar_ditempat'): ?>
                                        <i class="fas fa-money-bill-wave"></i> Bayar di Tempat
                                    <?php else: ?>
                                        <i class="fas fa-credit-card"></i> Transfer
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <div><?= date('d/m/Y', strtotime($row['tanggal'])) ?></div>
                                <small><?= date('H:i', strtotime($row['tanggal'])) ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px;">
                                <i class="fas fa-shopping-cart" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
                                Tidak ada data pesanan
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

<div class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 PustakaStore | Toko Buku Online Indonesia</p>
        </div>
    </div>
</div>

</body>
</html>
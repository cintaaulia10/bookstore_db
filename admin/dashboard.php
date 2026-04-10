<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/koneksi.php';

// fungsi hitung
function countData($koneksi, $table) {
    $q = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $table");
    if($q) {
        $d = mysqli_fetch_assoc($q);
        return $d['total'] ?? 0;
    }
    return 0;
}

// ambil data
$total_buku     = countData($koneksi, 'buku');
$total_kategori = countData($koneksi, 'kategori');
$total_user     = countData($koneksi, 'users');
$total_pesanan  = countData($koneksi, 'pesanan');

$nama_admin = $_SESSION['username'] ?? 'Admin';

// Ambil data tambahan untuk dashboard
$total_pesanan_baru = 0;
$query_pesanan_baru = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'pending' OR status = 'menunggu'");
if($query_pesanan_baru) {
    $data = mysqli_fetch_assoc($query_pesanan_baru);
    $total_pesanan_baru = $data['total'] ?? 0;
}

// Ambil 5 pesanan terbaru
$query_pesanan_terbaru = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY id DESC LIMIT 5");
$pesanan_terbaru = [];
if($query_pesanan_terbaru) {
    while($row = mysqli_fetch_assoc($query_pesanan_terbaru)) {
        $pesanan_terbaru[] = $row;
    }
}

// Ambil 5 buku terbaru
$query_buku_terbaru = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id DESC LIMIT 5");
$buku_terbaru = [];
if($query_buku_terbaru) {
    while($row = mysqli_fetch_assoc($query_buku_terbaru)) {
        $buku_terbaru[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BookStore Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fb; color: #1e293b; }
        
        /* HEADER */
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
        
        /* SEARCH */
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
        
        /* LAYOUT */
        .wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR WARNA PUTIH TANPA ICON */
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
        
        /* CONTENT */
        .content { flex: 1; padding: 30px 40px; }
        .content h2 { font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .content > p { color: #64748b; margin-bottom: 30px; }
        
        /* CARDS */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: rgba(0,0,0,0.03);
            border-radius: 0 0 0 100%;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card h4 { 
            font-size: 14px; 
            color: #64748b; 
            margin-bottom: 12px; 
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card h2 { 
            font-size: 36px; 
            font-weight: 700;
            margin-bottom: 8px;
        }
        .card .trend {
            font-size: 12px;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .card-1 { border-color: #3498db; }
        .card-2 { border-color: #27ae60; }
        .card-3 { border-color: #e74c3c; }
        .card-4 { border-color: #f39c12; }
        
        /* TABEL SECTION */
        .recent-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .recent-box {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .recent-box h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            color: #1e293b;
        }
        .recent-table {
            width: 100%;
            overflow-x: auto;
        }
        .recent-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-table th {
            text-align: left;
            padding: 12px 8px;
            background: #f8fafc;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }
        .recent-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending, .status-menunggu {
            background: #fef3c7;
            color: #d97706;
        }
        .status-processing, .status-diproses {
            background: #dbeafe;
            color: #2563eb;
        }
        .status-completed, .status-selesai {
            background: #d1fae5;
            color: #059669;
        }
        .btn-view {
            color: #3498db;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-view:hover {
            text-decoration: underline;
        }
        
        /* FOOTER */
        .footer { background: #0f172a; color: #cbd5e1; padding: 20px 0; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .recent-section {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) { 
            .container { padding: 0 20px; } 
            .wrapper { flex-direction: column; } 
            .sidebar { width: 100%; height: auto; position: static; } 
            .content { padding: 20px; }
            .cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }
        @media (max-width: 480px) {
            .cards {
                grid-template-columns: 1fr;
            }
            .header-content {
                flex-wrap: wrap;
            }
            .search-wrapper {
                order: 3;
                max-width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="container header-content">
        <div class="logo">
            <div class="logo-circle">
                <img src="../assets/css/images/logo_buku.png" alt="Logo">
            </div>
            <h2>PustakaStore</h2>
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

<!-- MAIN -->
<div class="wrapper">

    <!-- SIDEBAR WARNA PUTIH TANPA ICON -->
    <div class="sidebar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="kelola_buku.php">Kelola Buku</a>
        <a href="kelola_kategori.php">Kategori</a>
        <a href="kelola_user.php">User</a>
        <a href="kelola_pesanan.php">Pesanan</a>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <h2>Dashboard Admin</h2>
        <p>Selamat datang kembali, <?= htmlspecialchars($nama_admin) ?>! Senang melihat Anda kembali.</p>

        <!-- STATISTIK CARDS (4 CARD, TANPA PENDAPATAN) -->
        <div class="cards">
            <div class="card card-1">
                <h4><i class="fas fa-book"></i> Total Buku</h4>
                <h2><?= $total_buku ?></h2>
                <div class="trend">
                    <i class="fas fa-database"></i> Di database
                </div>
            </div>

            <div class="card card-2">
                <h4><i class="fas fa-tags"></i> Kategori</h4>
                <h2><?= $total_kategori ?></h2>
                <div class="trend">
                    <i class="fas fa-plus-circle"></i> Kategori aktif
                </div>
            </div>

            <div class="card card-3">
                <h4><i class="fas fa-users"></i> User Terdaftar</h4>
                <h2><?= $total_user ?></h2>
                <div class="trend">
                    <i class="fas fa-user-plus"></i> Member aktif
                </div>
            </div>

            <div class="card card-4">
                <h4><i class="fas fa-shopping-cart"></i> Total Pesanan</h4>
                <h2><?= $total_pesanan ?></h2>
                <div class="trend">
                    <i class="fas fa-clock"></i> <?= $total_pesanan_baru ?> pesanan baru
                </div>
            </div>
        </div>

        <!-- PESANAN BARU ALERT -->
        <?php if($total_pesanan_baru > 0): ?>
        <div style="background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px 20px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <i class="fas fa-bell" style="color: #2563eb; margin-right: 10px;"></i>
                <strong style="color: #1e40af;">Ada <?= $total_pesanan_baru ?> pesanan baru!</strong> Segera proses pesanan yang masuk.
            </div>
            <a href="kelola_pesanan.php" style="color: #2563eb; text-decoration: none; font-weight: 600;">Lihat Pesanan →</a>
        </div>
        <?php endif; ?>

        <!-- RECENT DATA SECTION -->
        <div class="recent-section">
            <!-- Pesanan Terbaru -->
            <div class="recent-box">
                <h3>Pesanan Terbaru</h3>
                <div class="recent-table">
                    <?php if(count($pesanan_terbaru) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pesanan_terbaru as $pesanan): ?>
                            <tr>
                                <td>#<?= $pesanan['id'] ?></td>
                                <td>Rp <?= number_format($pesanan['total_harga'] ?? 0, 0, ',', '.') ?></td>
                                <td>
                                    <span class="status-badge status-<?= $pesanan['status'] ?>">
                                        <?= ucfirst($pesanan['status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td>
                                  
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px;">Belum ada pesanan</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Buku Terbaru -->
            <div class="recent-box">
                <h3>Buku Terbaru</h3>
                <div class="recent-table">
                    <?php if(count($buku_terbaru) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($buku_terbaru as $buku): ?>
                            <tr>
                                <td><?= htmlspecialchars(substr($buku['judul'] ?? '-', 0, 30)) ?>...</td>
                                <td><?= htmlspecialchars($buku['penulis'] ?? '-') ?></td>
                                <td>Rp <?= number_format($buku['harga'] ?? 0, 0, ',', '.') ?></td>
                                <td>
                                    <a href="edit_buku.php?id=<?= $buku['id'] ?>" class="btn-view">
                                        Edit <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 40px;">Belum ada buku</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- FOOTER -->
<div class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 PustakaStore | Toko Buku Online Indonesia</p>
        </div>
    </div>
</div>

</body>
</html>
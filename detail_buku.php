<?php
session_start();
include 'config/koneksi.php';

$id_buku = $_GET['id'] ?? 0;
$buku = $koneksi->query("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id WHERE b.id = $id_buku")->fetch_assoc();
if(!$buku) {
    header("Location: index.php");
    exit();
}

$kategori = $buku['nama_kategori'] ?? 'Umum';
$stok = $buku['stok'] ?? 0;
$deskripsi = $buku['deskripsi'] ?? 'Belum ada deskripsi untuk buku ini.';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $qty = max(1, intval($_POST['qty'] ?? 1));
    $id_user = $_SESSION['user_id'];
    
    if($qty > $stok) {
        echo "<script>alert('Stok tidak mencukupi! Stok tersedia: $stok'); window.history.back();</script>";
        exit();
    }
    
    $cek_user = $koneksi->query("SELECT id FROM users WHERE id = $id_user");
    if($cek_user->num_rows == 0) {
        header("Location: login.php");
        exit();
    }
    
    $cek = $koneksi->query("SELECT id, jumlah FROM keranjang WHERE id_user = $id_user AND id_buku = $id_buku");
    if($cek && $cek->num_rows > 0) {
        $row = $cek->fetch_assoc();
        $jumlah_baru = $row['jumlah'] + $qty;
        if($jumlah_baru > $stok) {
            echo "<script>alert('Stok tidak mencukupi! Stok tersedia: $stok'); window.history.back();</script>";
            exit();
        }
        $koneksi->query("UPDATE keranjang SET jumlah = $jumlah_baru WHERE id = {$row['id']}");
    } else {
        $koneksi->query("INSERT INTO keranjang (id_user, id_buku, jumlah) VALUES ($id_user, $id_buku, $qty)");
    }

    if($_POST['action'] === 'buy') {
        header("Location: user/checkout.php");
        exit();
    }

    header("Location: user/keranjang.php");
    exit();
}

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($buku['judul']) ?> - BookStore</title>
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
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: 20px;
        }
        .nav-menu a {
            text-decoration: none;
            color: rgba(255,255,255,0.88);
            font-weight: 500;
            font-size: 15px;
            transition: 0.3s;
        }
        .nav-menu a:hover { color: #f8fafc; }
        
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
        
        .user-actions {
            display: flex; align-items: center; gap: 18px; color: white;
        }
        .user-actions a {
            text-decoration: none; color: rgba(255,255,255,0.92); font-weight: 500; font-size: 14px;
            transition: 0.3s;
        }
        .user-actions a:hover { color: #f8fafc; }
        .user-actions span {
            display: inline-flex; align-items: center; gap: 8px;
            color: rgba(255,255,255,0.92); font-weight: 500; font-size: 14px;
        }
        .cart-icon { position: relative; font-size: 18px; }
        .cart-count {
            position: absolute; top: -10px; right: -12px; background: #e74c3c;
            color: white; font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 50%;
        }
        .btn-outline {
            background: transparent; border: 1.5px solid #3498db; padding: 6px 18px;
            border-radius: 30px; color: #3498db !important;
        }
        .btn-outline:hover { background: #3498db; color: white !important; }
        .btn-primary-nav {
            background: #3498db; color: white !important; padding: 6px 18px; border-radius: 30px;
        }
        .btn-primary-nav:hover { background: #2980b9; }
        
        .detail-card { 
            background: white; 
            border-radius: 24px; 
            padding: 40px; 
            display: flex; 
            gap: 40px; 
            flex-wrap: wrap; 
            margin: 40px 0; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .book-cover { 
            flex: 0.4; 
            background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 400px;
            overflow: hidden;
        }
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .book-info { flex: 0.6; }
        .book-title { 
            font-family: 'Poppins', sans-serif;
            font-size: 32px; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        .book-author { 
            color: #64748b; 
            margin-bottom: 15px; 
            font-size: 16px; 
        }
        .book-price { 
            font-size: 28px; 
            color: #e74c3c; 
            font-weight: 700; 
            margin: 20px 0; 
        }
        .rating { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            margin-bottom: 20px; 
        }
        .rating i { color: #f39c12; }
        .rating span { color: #64748b; font-size: 13px; }
        
        .deskripsi {
            margin: 20px 0;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .deskripsi h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1e293b;
        }
        .deskripsi p {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            text-align: justify;
        }
        
        .kategori-text {
            margin: 10px 0;
            color: #475569;
            font-size: 14px;
        }
        .kategori-text strong {
            font-weight: 700;
            color: #1e293b;
        }
        
        .btn { 
            display: inline-block; 
            padding: 12px 30px; 
            background: #3498db; 
            color: white; 
            text-decoration: none; 
            border-radius: 40px; 
            font-weight: 600; 
            border: none; 
            cursor: pointer; 
            font-size: 14px;
        }
        .btn:hover { background: #2980b9; }
        .btn-secondary { 
            background: #f97316; 
            margin-left: 12px; 
        }
        .btn-secondary:hover { background: #ea580c; }
        .btn-disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
        .btn-disabled:hover {
            background: #94a3b8;
            transform: none;
        }
        .quantity-field { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            margin: 24px 0 10px; 
        }
        .quantity-field label { 
            font-weight: 600; 
            color: #334155; 
        }
        .quantity-field input { 
            width: 80px; 
            padding: 10px 14px; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            font-size: 14px;
        }
        .stock-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin: 10px 0 15px 0;
        }
        .stock-available {
            background: #d1fae5;
            color: #059669;
        }
        .stock-limited {
            background: #fef3c7;
            color: #d97706;
        }
        .stock-out {
            background: #fee2e2;
            color: #dc2626;
        }
        .action-buttons {
            margin-top: 10px;
        }
        .back-link { 
            display: inline-block; 
            margin-top: 20px; 
            color: #3498db; 
            text-decoration: none; 
        }
        .back-link:hover { text-decoration: underline; }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) {
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .detail-card { flex-direction: column; padding: 20px; }
            .book-cover { flex: 1 !important; min-height: 250px; }
            .book-info { flex: 1 !important; }
            .book-title { font-size: 24px; }
            .book-price { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container header-content">
        <div class="logo">
            <div class="logo-circle">
                <img src="assets/css/images/logo_buku.png" alt="Logo">
            </div>
            <h2>PustakaStore</h2>
        </div>
        
        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="tentang.php">Tentang Kami</a>
            <a href="kontak.php">Kontak</a>
        </div>
        
        <div class="search-wrapper">
            <form action="pencarian.php" method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="kata_kunci" placeholder="Cari judul buku...">
            </form>
        </div>
        
        <div class="user-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span><i class="fas fa-user-circle"></i> Halo, <?= htmlspecialchars($nama_user) ?></span>
                <a href="user/keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    if(isset($_SESSION['user_id'])) {
                        $cart_count = $koneksi->query("SELECT SUM(jumlah) as total FROM keranjang WHERE id_user = {$_SESSION['user_id']}")->fetch_assoc();
                        if($cart_count && $cart_count['total'] > 0) echo "<span class='cart-count'>{$cart_count['total']}</span>";
                    }
                    ?>
                </a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="registrasi.php" class="btn-primary-nav">Daftar</a>
                <a href="login.php" class="btn-outline">Masuk</a>
                <a href="user/keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="detail-card">
        <div class="book-cover">
            <?php 
            if(!empty($buku['gambar']) && file_exists("assets/css/uploads/" . $buku['gambar'])): 
            ?>
                <img src="assets/css/uploads/<?= $buku['gambar'] ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
            <?php else: ?>
                <i class="fas fa-book" style="font-size: 80px; color: #4f46e5;"></i>
            <?php endif; ?>
        </div>
        <div class="book-info">
            <h1 class="book-title"><?= htmlspecialchars($buku['judul']) ?></h1>
            <p class="book-author">Penulis: <?= htmlspecialchars($buku['penulis'] ?: 'Tidak diketahui') ?></p>
            
            <div class="rating">
                <span style="font-weight:700; color:#1e293b;">4.9</span>
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                <span>(120 ulasan)</span>
            </div>
            
            <div class="book-price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>
            
            <!-- Deskripsi Buku -->
            <div class="deskripsi">
                <h3>Deskripsi Buku</h3>
                <p><?= nl2br(htmlspecialchars($deskripsi)) ?></p>
            </div>
            
            <!-- Kategori (bold seperti Deskripsi) -->
            <div class="kategori-text">
                <strong>Kategori:</strong> <?= htmlspecialchars($kategori) ?>
            </div>

            <?php if($stok > 0): ?>
            <form method="POST" style="margin-top:20px;">
                <div class="quantity-field">
                    <label>Jumlah</label>
                    <input type="number" name="qty" value="1" min="1" max="<?= $stok ?>">
                </div>
                <!-- Stok di bawah jumlah -->
                <?php if($stok > 5): ?>
                    <div class="stock-status stock-available">Stok Tersedia (<?= $stok ?> buku)</div>
                <?php elseif($stok > 0): ?>
                    <div class="stock-status stock-limited">Stok Terbatas (<?= $stok ?> buku)</div>
                <?php endif; ?>
                
                <!-- Tombol di bawah stok -->
                <div class="action-buttons">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button type="submit" name="action" value="cart" class="btn">Masukkan Keranjang</button>
                        <button type="submit" name="action" value="buy" class="btn btn-secondary">Beli Sekarang</button>
                    <?php else: ?>
                        <a href="login.php" class="btn">Login untuk Membeli</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php else: ?>
                <div class="stock-status stock-out">Stok Habis</div>
                <div class="action-buttons">
                    <button class="btn btn-disabled" disabled>Stok Habis</button>
                </div>
            <?php endif; ?>

            <a href="index.php" class="back-link">← Kembali ke Beranda</a>
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
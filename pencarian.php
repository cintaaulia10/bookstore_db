<?php
session_start();
include 'config/koneksi.php';

$kata_kunci = isset($_GET['kata_kunci']) ? mysqli_real_escape_string($koneksi, $_GET['kata_kunci']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

if($kata_kunci) {
    $query = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id WHERE b.judul LIKE '%$kata_kunci%' OR b.penulis LIKE '%$kata_kunci%' ORDER BY b.id DESC";
} elseif($kategori) {
    $query = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id WHERE k.nama_kategori LIKE '%$kategori%' ORDER BY b.id DESC";
} else {
    $query = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id ORDER BY b.id DESC";
}

$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Pencarian - PustakaStore</title>
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
        
        .page-title {
            margin: 40px 0 30px 0;
        }
        .page-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }
        .book-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            padding: 20px;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .book-cover {
            width: 100%;
            height: 220px;
            background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .book-title {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .book-author {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 10px;
        }
        .book-price {
            font-size: 18px;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 12px;
        }
        .btn-detail {
            background: #ecfdf5;
            color: #10b981;
            border: none;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-detail:hover { background: #10b981; color: white; }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) {
            .books-grid { grid-template-columns: repeat(2, 1fr); }
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .books-grid { grid-template-columns: 1fr; }
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
                <input type="text" name="kata_kunci" placeholder="Cari judul buku..." value="<?= htmlspecialchars($kata_kunci) ?>">
            </form>
        </div>
        
        <div class="user-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span><i class="fas fa-user-circle"></i> Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="user/keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
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
    <div class="page-title">
        <h2>Hasil Pencarian: <?= htmlspecialchars($kata_kunci ?: $kategori) ?></h2>
    </div>
    
    <?php if($result && $result->num_rows > 0): ?>
    <div class="books-grid">
        <?php while($buku = $result->fetch_assoc()): ?>
        <div class="book-card">
            <div class="book-cover">
                <?php if(!empty($buku['gambar']) && file_exists("assets/css/uploads/" . $buku['gambar'])): ?>
                    <img src="assets/css/uploads/<?= $buku['gambar'] ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
                <?php else: ?>
                    <i class="fas fa-book" style="font-size: 48px; color: #4f46e5;"></i>
                <?php endif; ?>
            </div>
            <div class="book-title"><?= htmlspecialchars($buku['judul']) ?></div>
            <div class="book-author"><?= htmlspecialchars($buku['penulis'] ?: 'Penulis Terkenal') ?></div>
            <div class="book-price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>
            <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail →</a>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 80px; background: white; border-radius: 20px;">
        <i class="fas fa-search" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
        <p style="font-size: 18px; color: #64748b;">Buku tidak ditemukan</p>
        <p>Coba dengan kata kunci lain</p>
    </div>
    <?php endif; ?>
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
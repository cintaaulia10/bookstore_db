<?php
session_start();
include 'config/koneksi.php';

// Ambil data buku best seller (4 buku spesifik)
$best_seller = $koneksi->query("SELECT * FROM buku WHERE id IN (1, 5, 7, 6) ORDER BY FIELD(id, 1, 5, 7, 6)");
// id 1: Laskar Pelangi, id 5: Atomic Habits, id 7: Sebuah Seni Untuk Bersikap Bodo Amat, id 6: Filosofi Teras

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStore - Toko Buku Online</title>
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
        
        /* HERO SECTION */
        .hero {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 24px;
            margin: 40px 0;
            padding: 50px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }
        .hero-content { flex: 1; color: white; }
        .hero-content h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 38px; font-weight: 700; margin-bottom: 15px; line-height: 1.2;
        }
        .hero-content p { font-size: 16px; opacity: 0.9; margin-bottom: 25px; line-height: 1.6; }
        .hero-btn {
            background: #f39c12; color: #1e293b; padding: 12px 32px; border-radius: 40px;
            text-decoration: none; font-weight: 600; display: inline-block; transition: 0.3s;
        }
        .hero-btn:hover { background: #e67e22; transform: translateY(-2px); }
        .hero-image { flex: 0.5; text-align: center; }
        .hero-image img { 
            max-width: 100%; 
            height: 250px; 
            object-fit: cover;
            border-radius: 16px; 
            box-shadow: 0 20px 35px rgba(0,0,0,0.2);
            background: white;
        }
        
        /* SECTION TITLE */
        .section-title { margin: 50px 0 30px 0; }
        .section-title h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px; font-weight: 700;
            color: #1e293b; position: relative; display: inline-block;
        }
        .section-title h3:after {
            content: ''; position: absolute; bottom: -8px; left: 0;
            width: 60px; height: 3px; background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 3px;
        }
        
        /* BEST SELLER GRID - TINGGI MENYESUAIKAN ISI BUKU */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
            align-items: start;
        }
        .book-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .best-seller-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f39c12;
            color: #1e293b;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            z-index: 10;
        }
        .book-cover {
            width: 100%;
            height: 260px; /* tinggi seragam */
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 10px;
        }

        .book-cover img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .book-cover-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
            color: #4f46e5;
            font-size: 48px;
        }
        .book-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .book-title {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
            line-height: 1.3;
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
        .book-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
        }
        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #fef9e7;
            padding: 5px 10px;
            border-radius: 30px;
        }
        .rating i { color: #f39c12; font-size: 12px; }
        .rating span { font-size: 13px; font-weight: 600; color: #f39c12; }
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
            transition: 0.3s;
        }
        .btn-detail:hover { background: #10b981; color: white; }
        
        /* KATEGORI SECTION */
        .categories-section {
            background: white;
            border-radius: 24px;
            padding: 40px;
            margin: 50px 0;
            text-align: center;
        }
        .categories-section h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .categories-grid {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        .category-item {
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .category-item:hover { transform: translateY(-5px); }
        .category-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            transition: 0.3s;
        }
        .category-circle i { font-size: 40px; color: white; }
        .category-item h4 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .category-item p { font-size: 12px; color: #94a3b8; }
        
        /* FOOTER - HANYA COPYRIGHT */
        .footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 30px 0;
            margin-top: 60px;
        }
        .footer-bottom {
            text-align: center;
            font-size: 14px;
        }
        
        @media (max-width: 1024px) {
            .books-grid { grid-template-columns: repeat(2, 1fr); }
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .books-grid { grid-template-columns: 1fr; }
            .hero { padding: 30px 20px; }
            .hero-content h2 { font-size: 28px; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
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
                        if($cart_count['total'] > 0) echo "<span class='cart-count'>{$cart_count['total']}</span>";
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
    <!-- HERO SECTION -->
    <div class="hero">
        <div class="hero-content">
            <h2>Temukan Buku Terbaik<br>untuk Kamu</h2>
             <p>
        PustakaStore adalah platform toko buku online yang menyediakan berbagai koleksi buku berkualitas mulai dari buku pelajaran, novel, hingga buku nonfiksi.
            </p>
            <a href="belanja.php" class="hero-btn">Mulai Belanja →</a>
        </div>
        <div class="hero-image">
           <img src="assets/css/uploads/cover.jpg" alt="Ilustrasi Buku">
        </div>
    </div>

    <!-- BEST SELLER SECTION -->
    <div class="section-title">
        <h3>Best Seller Book</h3>
    </div>
    <div class="books-grid">
        <?php while($buku = $best_seller->fetch_assoc()): ?>
        <div class="book-card">
            <div class="best-seller-badge">
                <i class="fas fa-crown"></i> BEST SELLER
            </div>
            <div class="book-cover">
                <?php 
                if(!empty($buku['gambar']) && file_exists("assets/css/uploads/" . $buku['gambar'])): 
                ?>
                    <img src="assets/css/uploads/<?= $buku['gambar'] ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
                <?php else: ?>
                    <div class="book-cover-placeholder">
                        <i class="fas fa-book"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="book-info">
                <div class="book-title"><?= htmlspecialchars($buku['judul']) ?></div>
                <div class="book-author"><?= htmlspecialchars($buku['penulis'] ?: 'Penulis Terkenal') ?></div>
                <div class="book-price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>
                <div class="book-footer">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <span>4.9</span>
                    </div>
                    <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail →</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- KATEGORI POPULER -->
    <div class="categories-section">
        <h3>Kategori Buku</h3>
        <div class="categories-grid">
            <div class="category-item" onclick="location.href='pencarian.php?kategori=fiksi'">
                <div class="category-circle"><i class="fas fa-book-open"></i></div>
                <h4>Fiksi</h4>
                <p>Novel & Cerita</p>
            </div>
            <div class="category-item" onclick="location.href='pencarian.php?kategori=non-fiksi'">
                <div class="category-circle"><i class="fas fa-globe"></i></div>
                <h4>Non-Fiksi</h4>
                <p>Pengetahuan & Edukasi</p>
            </div>
            <div class="category-item" onclick="location.href='pencarian.php?kategori=anak-anak'">
                <div class="category-circle"><i class="fas fa-child"></i></div>
                <h4>Anak-Anak</h4>
                <p>Cerita Anak & Dongeng</p>
            </div>
            <div class="category-item" onclick="location.href='pencarian.php?kategori=pendidikan'">
                <div class="category-circle"><i class="fas fa-graduation-cap"></i></div>
                <h4>Pendidikan</h4>
                <p>Buku Sekolah & Akademik</p>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER - HANYA COPYRIGHT -->
<div class="footer">
    <div class="container">
        <div class="footer-bottom">2025 PustakaStore | Toko Buku Online Indonesia
            <p>&copy; </p>
        </div>
    </div>
</div>

</body>
</html>
<?php 
session_start();
include 'config/koneksi.php';

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - BookStore</title>
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
        .nav-menu a.active { color: #3498db; }
        
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
        
        /* PAGE TITLE */
        .page-title {
            margin: 40px 0 30px 0;
        }
        .page-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            position: relative;
            display: inline-block;
        }
        .page-title h2:after {
            content: ''; position: absolute; bottom: -8px; left: 0;
            width: 60px; height: 3px; background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 3px;
        }
        
        /* ABOUT SECTION */
        .about-container {
            background: white;
            border-radius: 24px;
            padding: 50px;
            margin-bottom: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .about-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .about-header .icon {
            font-size: 64px;
            color: #3498db;
            margin-bottom: 20px;
        }
        .about-header h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .about-header p {
            color: #64748b;
            font-size: 16px;
        }
        .about-content {
            max-width: 800px;
            margin: 0 auto;
        }
        .about-text {
            line-height: 1.8;
            color: #475569;
            text-align: center;
        }
        .about-text p {
            margin-bottom: 20px;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }
        .feature-card {
            text-align: center;
            padding: 25px 20px;
            background: #f8fafc;
            border-radius: 16px;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-card i {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }
        .feature-card h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1e293b;
        }
        .feature-card p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }
        
        .quote {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-top: 40px;
            color: white;
        }
        .quote i {
            font-size: 40px;
            opacity: 0.5;
            margin-bottom: 15px;
        }
        .quote p {
            font-size: 18px;
            font-style: italic;
            line-height: 1.6;
        }
        .quote h4 {
            margin-top: 15px;
            font-weight: 600;
        }
        
        /* FOOTER */
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) {
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
            .features { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .about-container { padding: 30px 20px; }
            .features { grid-template-columns: 1fr; }
            .quote p { font-size: 14px; }
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
            <a href="tentang.php" class="active">Tentang Kami</a>
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
    <div class="page-title">
        <h2>Tentang Kami</h2>
    </div>

    <div class="about-container">
        <div class="about-header">
            <div class="icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h2>PustakaStore</h2>
            <p>Toko Buku Online Terpercaya di Indonesia</p>
        </div>
        
        <div class="about-content">
            <div class="about-text">
                <p>BookStore didirikan pada tahun 2025 dengan misi untuk menyediakan akses mudah ke buku-buku berkualitas bagi masyarakat Indonesia. Kami percaya bahwa membaca adalah jendela dunia, dan setiap orang berhak mendapatkan buku favorit mereka dengan harga terbaik.</p>
                <p>Kami terus berinovasi untuk memberikan pengalaman berbelanja buku yang nyaman, aman, dan menyenangkan. Dengan koleksi ribuan judul buku dari berbagai genre, kami siap menemani perjalanan literasi Anda.</p>
            </div>
            
            <div class="features">
                <div class="feature-card">
                    <i class="fas fa-book"></i>
                    <h4>Ribuan Koleksi</h4>
                    <p>Lebih dari 5000+ judul buku dari berbagai genre dan penerbit terbaik</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <h4>Pengiriman Cepat</h4>
                    <p>Layanan pengiriman ke seluruh Indonesia dengan harga terjangkau</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Bayar di Tempat</h4>
                    <p>Metode pembayaran COD yang aman dan nyaman untuk Anda</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-gem"></i>
                    <h4>Buku Original</h4>
                    <p>100% buku original dan berkualitas dari penerbit resmi</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h4>Layanan 24/7</h4>
                    <p>Tim customer service siap membantu Anda setiap saat</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tags"></i>
                    <h4>Harga Terbaik</h4>
                    <p>Harga kompetitif dan diskon menarik setiap bulannya</p>
                </div>
            </div>
            
            <div class="quote">
                <i class="fas fa-quote-left"></i>
                <p>"Membaca adalah jendela dunia. BookStore hadir untuk membuka jendela itu bagi seluruh masyarakat Indonesia."</p>
                <h4>- Tim PustakaStore</h4>
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
<?php
session_start();
include 'config/koneksi.php';

$pesan_hasil = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!isset($_SESSION['user_id'])) {
        $error = "Silakan login terlebih dahulu untuk mengirim pesan.";
    } else {
        $id_user = $_SESSION['user_id'];
        $subjek = mysqli_real_escape_string($koneksi, $_POST['subjek']);
        $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan']);
        
        if(empty($subjek) || empty($pesan)) {
            $error = "Semua field harus diisi!";
        } else {
            $query = "INSERT INTO pesan_kontak (id_user, subjek, pesan) VALUES ('$id_user', '$subjek', '$pesan')";
            if($koneksi->query($query)) {
                $pesan_hasil = "Pesan Anda berhasil dikirim ke admin! Kami akan merespon segera.";
            } else {
                $error = "Gagal mengirim pesan. Silakan coba lagi.";
            }
        }
    }
}

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - BookStore</title>
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
        
        /* CONTACT SECTION */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .contact-info {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .contact-info h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-icon {
            width: 50px;
            height: 50px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-icon i {
            font-size: 22px;
            color: #3498db;
        }
        .info-text h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-text p {
            font-size: 14px;
            color: #64748b;
        }
        
        .contact-form {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .contact-form h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #334155;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .btn-send {
            width: 100%;
            background: #f97316;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-send:hover {
            background: #ea580c;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #059669;
            border-left: 4px solid #059669;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .map-container {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .map-container h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .map-placeholder {
            background: #e2e8f0;
            border-radius: 16px;
            padding: 60px;
            color: #64748b;
        }
        
        /* FOOTER */
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) {
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
            .contact-wrapper { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
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
            <h2> PustakaStore</h2>
        </div>
        
        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="tentang.php">Tentang Kami</a>
            <a href="kontak.php" class="active">Kontak</a>
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
        <h2>Kontak Kami</h2>
    </div>
    
    <div class="contact-wrapper">
        <!-- Informasi Kontak -->
        <div class="contact-info">
            <h3><i class="fas fa-address-card"></i> Informasi Kontak</h3>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-text">
                    <h4>Alamat</h4>
                    <p>Jl. Contoh No. 123, Jakarta Selatan, Indonesia</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="info-text">
                    <h4>Telepon</h4>
                    <p>(021) 12345678 / 081234567890</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-text">
                    <h4>Email</h4>
                    <p>cs@bookstore.com</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-text">
                    <h4>Jam Operasional</h4>
                    <p>Senin - Sabtu, 09:00 - 17:00</p>
                </div>
            </div>
        </div>
        
        <!-- Form Kirim Pesan -->
        <div class="contact-form">
            <h3><i class="fas fa-paper-plane"></i> Kirim Pesan</h3>
            
            <?php if($pesan_hasil): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $pesan_hasil ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Silakan <a href="login.php" style="color:#dc2626; font-weight:bold;">login</a> terlebih dahulu untuk mengirim pesan.
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Anda</label>
                        <input type="text" value="<?= htmlspecialchars($nama_user) ?>" readonly disabled style="background:#f1f5f9;">
                    </div>
                    <div class="form-group">
                        <label>Subjek</label>
                        <input type="text" name="subjek" required placeholder="Masukkan subjek pesan">
                    </div>
                    <div class="form-group">
                        <label>Pesan</label>
                        <textarea name="pesan" rows="5" required placeholder="Tulis pesan Anda di sini..."></textarea>
                    </div>
                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Lokasi -->
    <div class="map-container">
        <h3><i class="fas fa-map-marked-alt"></i> Lokasi Kami</h3>
        <div class="map-placeholder">
            <i class="fas fa-map" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            <p>Jl. Contoh No. 123, Jakarta Selatan</p>
            <p>Google Maps: -6.200000, 106.816666</p>
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
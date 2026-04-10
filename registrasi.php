<?php
session_start();
include 'config/koneksi.php';

$pesan_sukses = '';
$pesan_error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = md5($_POST['password']);
    $konfirmasi_password = md5($_POST['konfirmasi_password']);
    
    // Cek email sudah terdaftar
    $cek_email = $koneksi->query("SELECT id FROM users WHERE email = '$email'");
    if($cek_email->num_rows > 0) {
        $pesan_error = "Email sudah terdaftar!";
    } elseif($_POST['password'] != $_POST['konfirmasi_password']) {
        $pesan_error = "Password dan konfirmasi password tidak sama!";
    } else {
        $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'user')";
        if($koneksi->query($query)) {
            $pesan_sukses = "Registrasi berhasil! Silakan login.";
        } else {
            $pesan_error = "Registrasi gagal: " . $koneksi->error;
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
    <title>Daftar - BookStore</title>
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
        
        .register-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 180px);
            padding: 40px 0;
        }
        .register-container {
            max-width: 480px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .register-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .register-header h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .register-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .register-body {
            padding: 35px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .btn-register {
            width: 100%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .login-link p {
            font-size: 14px;
            color: #64748b;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: #d1fae5;
            color: #059669;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 1024px) {
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
        }
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .register-body { padding: 25px; }
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
            <h2> PustakaStore</h2>
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
                </a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-outline">Masuk</a>
                <a href="registrasi.php" class="btn-primary-nav">Daftar</a>
                <a href="user/keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2>Daftar Akun Baru</h2>
                <p>Bergabunglah dengan ribuan pembaca lainnya</p>
            </div>
            <div class="register-body">
                <?php if($pesan_sukses): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $pesan_sukses ?> <a href="login.php" style="color:#059669; font-weight:600; text-decoration:underline;">Login di sini</a>
                    </div>
                <?php endif; ?>
                
                <?php if($pesan_error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $pesan_error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="Masukkan username">
                    </div>
                    <div class="form-group">
                        <label>Alamat Email</label>
                        <input type="email" name="email" required placeholder="contoh@gmail.com">
                    </div>
                    <div class="form-group">
                        <label>Kata Sandi</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Kata Sandi</label>
                        <input type="password" name="konfirmasi_password" required placeholder="Ulangi kata sandi">
                    </div>
                    <button type="submit" class="btn-register">Daftar Sekarang</button>
                </form>
                
                <div class="login-link">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
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
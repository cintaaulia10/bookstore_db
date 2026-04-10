<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config/koneksi.php';

$id_user = $_SESSION['user_id'];

// Cek apakah user ada di tabel users
$cek_user = $koneksi->query("SELECT id, username, email FROM users WHERE id = $id_user");
if($cek_user->num_rows == 0) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
$user = $cek_user->fetch_assoc();

// Ambil keranjang
$keranjang = $koneksi->query("SELECT k.id as id_keranjang, k.jumlah, b.id as id_buku, b.judul, b.harga, b.gambar, b.penulis, b.stok 
                               FROM keranjang k 
                               JOIN buku b ON k.id_buku = b.id 
                               WHERE k.id_user = $id_user");

if($keranjang->num_rows == 0) {
    header("Location: keranjang.php");
    exit();
}

$total = 0;
$items = [];
while($row = $keranjang->fetch_assoc()) {
    $subtotal = $row['harga'] * $row['jumlah'];
    $total += $subtotal;
    $row['subtotal'] = $subtotal;
    $items[] = $row;
}

// Jika form disubmit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'bayar_ditempat';
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat'] ?? '');
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp'] ?? '');
    
    // Upload bukti pembayaran
    $gambar_bukti = '';
    if(isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $target_dir = "../assets/uploads/bukti/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $target_file)) {
            $gambar_bukti = $new_filename;
        }
    }
    
    // Buat pesanan
    $tanggal = date('Y-m-d H:i:s');
    $query_pesanan = "INSERT INTO pesanan (id_user, total_harga, metode_pembayaran, status, tanggal, alamat_pengiriman, no_hp, gambar_bukti) 
                       VALUES ($id_user, $total, '$metode_pembayaran', 'pending', '$tanggal', '$alamat', '$no_hp', '$gambar_bukti')";
    $koneksi->query($query_pesanan);
    $id_pesanan = $koneksi->insert_id;
    
    // Simpan detail pesanan dan kurangi stok
    foreach($items as $item) {
        // Simpan detail pesanan
        $koneksi->query("INSERT INTO detail_pesanan (id_pesanan, id_buku, jumlah, harga_satuan) 
                         VALUES ($id_pesanan, {$item['id_buku']}, {$item['jumlah']}, {$item['harga']})");
        
        // Kurangi stok buku
        $stok_baru = $item['stok'] - $item['jumlah'];
        $koneksi->query("UPDATE buku SET stok = $stok_baru WHERE id = {$item['id_buku']}");
    }
    
    // Kosongkan keranjang
    $koneksi->query("DELETE FROM keranjang WHERE id_user = $id_user");
    
    header("Location: pembayaran.php?pesanan_id=$id_pesanan");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BookStore</title>
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
            position: relative;
            display: inline-block;
        }
        .page-title h2:after {
            content: ''; position: absolute; bottom: -8px; left: 0;
            width: 60px; height: 3px; background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 3px;
        }
        
        .checkout-wrapper {
            display: grid;
            grid-template-columns: 1fr 0.8fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .checkout-form {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .checkout-form h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
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
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .bukti-pembayaran {
            display: none;
            margin-top: 10px;
        }
        .bukti-pembayaran.active {
            display: block;
        }
        .info-rekening {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #bbf7d0;
        }
        .info-rekening h4 {
            color: #166534;
            margin-bottom: 10px;
        }
        .info-rekening p {
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
        }
        .order-summary h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .order-item {
            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .order-item-img {
            width: 60px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .order-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .order-item-info p {
            font-size: 12px;
            color: #64748b;
        }
        .order-item-price {
            font-weight: 600;
            color: #e74c3c;
            font-size: 14px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            margin-top: 10px;
            border-top: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 18px;
            color: #e74c3c;
        }
        .btn-checkout {
            width: 100%;
            background: #f97316;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background: #ea580c;
            transform: translateY(-2px);
        }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 900px) {
            .checkout-wrapper { grid-template-columns: 1fr; }
            .container { padding: 0 20px; }
        }
        @media (max-width: 768px) {
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
        }
    </style>
    <script>
        function toggleBuktiPembayaran() {
            const metode = document.getElementById('metode_pembayaran').value;
            const buktiDiv = document.getElementById('bukti_pembayaran_div');
            const infoTransfer = document.getElementById('info_transfer');
            const infoEwallet = document.getElementById('info_ewallet');
            
            if(metode === 'transfer') {
                buktiDiv.classList.add('active');
                infoTransfer.style.display = 'block';
                infoEwallet.style.display = 'none';
            } else if(metode === 'ewallet') {
                buktiDiv.classList.add('active');
                infoTransfer.style.display = 'none';
                infoEwallet.style.display = 'block';
            } else {
                buktiDiv.classList.remove('active');
                infoTransfer.style.display = 'none';
                infoEwallet.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<div class="header">
    <div class="container header-content">
        <div class="logo">
            <div class="logo-circle">
                <img src="../assets/css/images/logo_buku.png" alt="Logo">
            </div>
            <h2>PustakaStore</h2>
        </div>
        
        <div class="nav-menu">
            <a href="../index.php">Beranda</a>
            <a href="../tentang.php">Tentang Kami</a>
            <a href="../kontak.php">Kontak</a>
        </div>
        
        <div class="search-wrapper">
            <form action="../pencarian.php" method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="kata_kunci" placeholder="Cari judul buku...">
            </form>
        </div>
        
        <div class="user-actions">
            <span><i class="fas fa-user-circle"></i> Halo, <?= htmlspecialchars($user['username']) ?></span>
            <a href="keranjang.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="page-title">
        <h2>Checkout</h2>
    </div>
    
    <div class="checkout-wrapper">
        <div class="checkout-form">
            <h3>Informasi Pengiriman</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly disabled style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 08123456789" required>
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap Anda" required></textarea>
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" onchange="toggleBuktiPembayaran()" required>
                        <option value="bayar_ditempat">Bayar di Tempat (COD)</option>
                        <option value="transfer">Transfer Bank (BCA/Mandiri/BRI)</option>
                        <option value="ewallet">QRIS / DANA / OVO</option>
                    </select>
                </div>
                
                <div id="info_transfer" style="display:none;" class="info-rekening">
                    <h4>Transfer ke Rekening Berikut:</h4>
                    <p><strong>Bank BCA:</strong> 1234567890</p>
                    <p><strong>Bank Mandiri:</strong> 9876543210</p>
                    <p><strong>Bank BRI:</strong> 1122334455</p>
                    <p><em>Setelah transfer, upload bukti pembayaran.</em></p>
                </div>
                
                <div id="info_ewallet" style="display:none;" class="info-rekening">
                    <h4>Pembayaran via E-Wallet / QRIS:</h4>
                    <p><strong>DANA / OVO / QRIS:</strong> 081234567890</p>
                    <p><em>Setelah pembayaran, upload bukti screenshot.</em></p>
                </div>
                
                <div id="bukti_pembayaran_div" class="bukti-pembayaran">
                    <div class="form-group">
                        <label>Upload Bukti Pembayaran</label>
                        <input type="file" name="bukti_bayar" accept="image/jpeg,image/png,image/jpg">
                        <small style="color:#64748b;">Upload foto bukti transfer / screenshot pembayaran</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-checkout">
                    Konfirmasi Pesanan
                </button>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Ringkasan Pesanan</h3>
            <?php foreach($items as $item): ?>
            <div class="order-item">
                <div class="order-item-img">
                    <?php if(!empty($item['gambar']) && file_exists("../assets/css/uploads/" . $item['gambar'])): ?>
                        <img src="../assets/css/uploads/<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['judul']) ?>">
                    <?php else: ?>
                        <i class="fas fa-book" style="font-size: 24px; color: #94a3b8;"></i>
                    <?php endif; ?>
                </div>
                <div class="order-item-info">
                    <h4><?= htmlspecialchars($item['judul']) ?></h4>
                    <p><?= htmlspecialchars($item['penulis'] ?: 'Penulis') ?></p>
                    <p><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                </div>
                <div class="order-item-price">
                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="summary-row">
                <span>Subtotal (<?= count($items) ?> item)</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <div class="summary-row">
                <span>Biaya Pengiriman</span>
                <span>Gratis</span>
            </div>
            <div class="summary-total">
                <span>Total Tagihan</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
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
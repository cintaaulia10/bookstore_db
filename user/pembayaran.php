<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config/koneksi.php';

$id_pesanan = $_GET['pesanan_id'] ?? 0;
$id_user = $_SESSION['user_id'];

// Ambil data pesanan
$pesanan = $koneksi->query("SELECT * FROM pesanan WHERE id = $id_pesanan AND id_user = $id_user")->fetch_assoc();
if(!$pesanan) {
    header("Location: ../index.php");
    exit();
}

// Ambil detail pesanan
$detail = $koneksi->query("SELECT d.*, b.judul, b.gambar, b.penulis 
                           FROM detail_pesanan d 
                           JOIN buku b ON d.id_buku = b.id 
                           WHERE d.id_pesanan = $id_pesanan");

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - BookStore</title>
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
        
        .btn-outline {
            background: transparent; border: 1.5px solid #3498db; padding: 6px 18px;
            border-radius: 30px; color: #3498db !important;
        }
        .btn-outline:hover { background: #3498db; color: white !important; }
        .btn-primary-nav {
            background: #3498db; color: white !important; padding: 6px 18px; border-radius: 30px;
        }
        .btn-primary-nav:hover { background: #2980b9; }
        
        .payment-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            margin: 40px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .payment-header .icon-success {
            font-size: 64px;
            color: #27ae60;
        }
        .payment-header h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            color: #27ae60;
            margin-top: 15px;
        }
        .payment-header p {
            color: #64748b;
            margin-top: 8px;
        }
        
        .order-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .order-info h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 500;
            color: #64748b;
        }
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .order-items {
            margin-bottom: 30px;
        }
        .order-items h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
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
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .order-item-info p {
            font-size: 12px;
            color: #64748b;
        }
        .order-item-price {
            text-align: right;
        }
        .order-item-price .price {
            font-weight: 700;
            color: #e74c3c;
            font-size: 16px;
        }
        .order-item-price .qty {
            font-size: 12px;
            color: #64748b;
        }
        
        .payment-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-info h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: #166534;
            margin-bottom: 10px;
        }
        .payment-info p {
            font-size: 14px;
            color: #14532d;
            margin-bottom: 8px;
        }
        .payment-info .bank-account {
            background: white;
            padding: 12px;
            border-radius: 12px;
            margin-top: 10px;
        }
        
        .total-section {
            text-align: right;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .total-section .total-label {
            font-size: 16px;
            color: #64748b;
        }
        .total-section .total-value {
            font-size: 28px;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-home:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .btn-print {
            background: #64748b;
            margin-left: 10px;
        }
        .btn-print:hover {
            background: #475569;
        }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 768px) {
            .container { padding: 0 20px; }
            .payment-card { padding: 20px; }
            .order-item { flex-wrap: wrap; }
            .order-item-price { text-align: left; margin-top: 10px; }
            .header-content { flex-wrap: wrap; }
            .nav-menu { order: 3; margin-left: 0; width: 100%; justify-content: center; padding-top: 10px; }
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
            <span><i class="fas fa-user-circle"></i> Halo, <?= htmlspecialchars($nama_user) ?></span>
            <a href="keranjang.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="payment-card">
        <div class="payment-header">
            <i class="fas fa-check-circle icon-success"></i>
            <h2>Pesanan Berhasil Dibuat!</h2>
            <p>Terima kasih telah berbelanja di BookStore</p>
        </div>
        
        <div class="order-info">
            <h3>Informasi Pesanan</h3>
            <div class="info-row">
                <span class="info-label">Nomor Pesanan</span>
                <span class="info-value">#<?= str_pad($pesanan['id'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pemesanan</span>
                <span class="info-value"><?= date('d F Y H:i', strtotime($pesanan['tanggal'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-pending">⏳ Pending (Menunggu Pembayaran)</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Metode Pembayaran</span>
                <span class="info-value">
                    <?php if($pesanan['metode_pembayaran'] == 'bayar_ditempat'): ?>
                        <i class="fas fa-money-bill-wave"></i> Bayar di Tempat (COD)
                    <?php else: ?>
                        <i class="fas fa-credit-card"></i> Transfer Bank
                    <?php endif; ?>
                </span>
            </div>
            <?php if(!empty($pesanan['alamat_pengiriman'])): ?>
            <div class="info-row">
                <span class="info-label">Alamat Pengiriman</span>
                <span class="info-value"><?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="order-items">
            <h3>Detail Buku yang Dipesan</h3>
            <?php while($item = $detail->fetch_assoc()): ?>
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
                    <p><?= htmlspecialchars($item['penulis'] ?? 'Penulis') ?></p>
                </div>
                <div class="order-item-price">
                    <div class="price">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></div>
                    <div class="qty">Jumlah: <?= $item['jumlah'] ?></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php if($pesanan['metode_pembayaran'] == 'transfer'): ?>
        <div class="payment-info">
            <h4><i class="fas fa-university"></i> Informasi Pembayaran Transfer</h4>
            <p>Silakan transfer ke rekening berikut sesuai dengan total tagihan:</p>
            <div class="bank-account">
                <p><strong>Bank BCA</strong></p>
                <p>Nomor Rekening: 1234567890</p>
                <p>Atas Nama: BookStore Indonesia</p>
            </div>
            <div class="bank-account">
                <p><strong>Bank Mandiri</strong></p>
                <p>Nomor Rekening: 9876543210</p>
                <p>Atas Nama: BookStore Indonesia</p>
            </div>
            <p style="margin-top: 10px; font-size: 12px;">*Setelah transfer, silakan konfirmasi ke admin melalui WhatsApp atau email.</p>
        </div>
        <?php else: ?>
        <div class="payment-info">
            <h4><i class="fas fa-store"></i> Informasi Pembayaran di Tempat</h4>
            <p>Silakan datang ke toko kami untuk melakukan pembayaran dan mengambil buku.</p>
            <p><strong>Alamat Toko:</strong> Jl. Contoh No. 123, Kota Contoh</p>
            <p><strong>Jam Operasional:</strong> Senin - Sabtu, 09:00 - 17:00</p>
        </div>
        <?php endif; ?>
        
        <div class="total-section">
            <span class="total-label">Total Tagihan:</span>
            <div class="total-value">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></div>
        </div>
        
        <div style="text-align: center;">
            <a href="../index.php" class="btn-home"><i class="fas fa-home"></i> Kembali ke Beranda</a>
            <a href="#" class="btn-home btn-print" onclick="window.print();"><i class="fas fa-print"></i> Cetak</a>
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
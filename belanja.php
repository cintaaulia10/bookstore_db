<?php
session_start();
include 'config/koneksi.php';

// Ambil semua buku dari database
$query_all = "SELECT * FROM buku ORDER BY id";
$result_all = $koneksi->query($query_all);
$all_books = [];
while($row = $result_all->fetch_assoc()) {
    $all_books[] = $row;
}

// Kelompokkan buku ke kategori
$buku_per_kategori = [
    'Fiksi' => array_slice($all_books, 0, 4),
    'Non Fiksi' => array_slice($all_books, 4, 4),
    'Anak-Anak' => array_slice($all_books, 8, 4),
    'Pendidikan' => array_slice($all_books, 12, 4)
];

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Buku - PustakaStore</title>
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
        
        /* HERO BANNER */
        .hero-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            margin: 40px 0;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }
        .hero-banner h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .hero-banner p {
            font-size: 16px;
            opacity: 0.95;
        }
        
        .page-title { margin: 40px 0 20px 0; }
        .page-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }
        
        .kategori-section { margin-bottom: 50px; }
        .kategori-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        .kategori-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
        }
        .kategori-header a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .kategori-header a:hover { text-decoration: underline; }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 40px;
            align-items: stretch;
        }
        .book-card {
            background: white;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .book-cover {
            width: 100%;
            height: 260px;
            background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            overflow: hidden;
        }
        .book-cover img {
            max-width: 100%;
            max-height: 100%;
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
            min-height: 48px;
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
        
        /* FOOTER */
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
            .hero-banner { padding: 30px 20px; }
            .hero-banner h2 { font-size: 28px; }
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
    <!-- HERO BANNER -->
    <div class="hero-banner">
        <h2><i class="fas fa-shopping-bag"></i> Belanja Buku</h2>
        <p>Temukan koleksi buku favorit Anda dari berbagai kategori</p>
    </div>

    <!-- KATEGORI BUKU: FIKSI -->
    <div class="kategori-section">
        <div class="kategori-header">
            <h3>Fiksi</h3>
            <a href="pencarian.php?kategori=fiksi"></a>
        </div>
        <div class="books-grid">
            <?php 
            $buku_list = $buku_per_kategori['Fiksi'];
            if(count($buku_list) > 0):
                foreach($buku_list as $buku):
            ?>
            <div class="book-card">
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
                            <span>4.8</span>
                        </div>
                        <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                for($i = 0; $i < 4; $i++):
            ?>
            <div class="book-card">
                <div class="book-cover">
                    <div class="book-cover-placeholder">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="book-info">
                    <div class="book-title">Contoh Buku Fiksi <?= $i+1 ?></div>
                    <div class="book-author">Penulis Contoh</div>
                    <div class="book-price">Rp 99.000</div>
                    <div class="book-footer">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>4.5</span>
                        </div>
                        <a href="#" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endfor;
            endif; 
            ?>
        </div>
    </div>

    <!-- KATEGORI BUKU: NON FIKSI -->
    <div class="kategori-section">
        <div class="kategori-header">
            <h3>Non Fiksi</h3>
            <a href="pencarian.php?kategori=non-fiksi"></a>
        </div>
        <div class="books-grid">
            <?php 
            $buku_list = $buku_per_kategori['Non Fiksi'];
            if(count($buku_list) > 0):
                foreach($buku_list as $buku):
            ?>
            <div class="book-card">
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
                            <span>4.7</span>
                        </div>
                        <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                for($i = 0; $i < 4; $i++):
            ?>
            <div class="book-card">
                <div class="book-cover">
                    <div class="book-cover-placeholder">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="book-info">
                    <div class="book-title">Contoh Buku Non Fiksi <?= $i+1 ?></div>
                    <div class="book-author">Penulis Contoh</div>
                    <div class="book-price">Rp 129.000</div>
                    <div class="book-footer">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>4.6</span>
                        </div>
                        <a href="#" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endfor;
            endif; 
            ?>
        </div>
    </div>

    <!-- KATEGORI BUKU: PENGEMBANGAN DIRI -->
    <div class="kategori-section">
        <div class="kategori-header">
            <h3>Anak-Anak</h3>
            <a href="pencarian.php?kategori=anak-anak"></a>
        </div>
        <div class="books-grid">
            <?php 
            $buku_list = $buku_per_kategori['Anak-Anak'];
            if(count($buku_list) > 0):
                foreach($buku_list as $buku):
            ?>
            <div class="book-card">
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
            <?php 
                endforeach;
            else:
                for($i = 0; $i < 4; $i++):
            ?>
            <div class="book-card">
                <div class="book-cover">
                    <div class="book-cover-placeholder">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="book-info">
                    <div class="book-title">Contoh Buku Anak-Anak<?= $i+1 ?></div>
                    <div class="book-author">Penulis Contoh</div>
                    <div class="book-price">Rp 149.000</div>
                    <div class="book-footer">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>4.9</span>
                        </div>
                        <a href="#" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endfor;
            endif; 
            ?>
        </div>
    </div>

    <!-- KATEGORI BUKU: PENDIDIKAN -->
    <div class="kategori-section">
        <div class="kategori-header">
            <h3>Pendidikan</h3>
            <a href="pencarian.php?kategori=pendidikan"></a>
        </div>
        <div class="books-grid">
            <?php 
            $buku_list = $buku_per_kategori['Pendidikan'];
            if(count($buku_list) > 0):
                foreach($buku_list as $buku):
            ?>
            <div class="book-card">
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
                            <span>4.8</span>
                        </div>
                        <a href="detail_buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                for($i = 0; $i < 4; $i++):
            ?>
            <div class="book-card">
                <div class="book-cover">
                    <div class="book-cover-placeholder">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="book-info">
                    <div class="book-title">Contoh Buku Pendidikan <?= $i+1 ?></div>
                    <div class="book-author">Penulis Contoh</div>
                    <div class="book-price">Rp 89.000</div>
                    <div class="book-footer">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span>4.7</span>
                        </div>
                        <a href="#" class="btn-detail">Detail →</a>
                    </div>
                </div>
            </div>
            <?php 
                endfor;
            endif; 
            ?>
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
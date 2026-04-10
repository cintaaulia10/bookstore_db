<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config/koneksi.php';

$id_user = $_SESSION['user_id'];

// Hapus item
if(isset($_GET['hapus'])) {
    $id_keranjang = (int)$_GET['hapus'];
    $koneksi->query("DELETE FROM keranjang WHERE id = $id_keranjang AND id_user = $id_user");
    header("Location: keranjang.php");
    exit();
}

// Update jumlah via POST
if(isset($_POST['update_jumlah'])) {
    $id_keranjang = (int)$_POST['id_keranjang'];
    $jumlah = max(1, intval($_POST['jumlah']));
    $koneksi->query("UPDATE keranjang SET jumlah = $jumlah WHERE id = $id_keranjang AND id_user = $id_user");
    header("Location: keranjang.php");
    exit();
}

// Hapus item terpilih
if(isset($_POST['hapus_terpilih'])) {
    if(!empty($_POST['pilih'])) {
        $ids = implode(',', array_map('intval', $_POST['pilih']));
        $koneksi->query("DELETE FROM keranjang WHERE id IN ($ids) AND id_user = $id_user");
    }
    header("Location: keranjang.php");
    exit();
}

// Ambil data keranjang
$keranjang = $koneksi->query("SELECT k.id as id_keranjang, k.jumlah, b.id as id_buku, b.judul, b.harga, b.penulis, b.gambar 
                               FROM keranjang k 
                               JOIN buku b ON k.id_buku = b.id 
                               WHERE k.id_user = $id_user 
                               ORDER BY k.id DESC");

$items = [];
$total = 0;
while($row = $keranjang->fetch_assoc()) {
    $subtotal = $row['harga'] * $row['jumlah'];
    $total += $subtotal;
    $row['subtotal'] = $subtotal;
    $items[] = $row;
}

$nama_user = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - BookStore</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
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
        
        .cart-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th {
            text-align: left;
            padding: 18px 20px;
            background: #f8fafc;
            font-weight: 600;
            font-size: 14px;
            color: #475569;
        }
        .cart-table td {
            padding: 20px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .product-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-image {
            width: 80px;
            height: 100px;
            background: linear-gradient(145deg, #e0e7ff, #c7d2fe);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-image-placeholder {
            font-size: 32px;
            color: #4f46e5;
        }
        .product-info h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1e293b;
        }
        .product-info p {
            font-size: 13px;
            color: #64748b;
        }
        
        .price { font-weight: 600; color: #1e293b; }
        
        .jumlah-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .jumlah-wrapper input {
            width: 60px;
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
        }
        .jumlah-wrapper button {
            background: #f1f5f9;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .jumlah-wrapper button:hover { background: #e2e8f0; }
        
        .subtotal {
            font-weight: 700;
            color: #e74c3c;
            font-size: 16px;
        }
        .aksi-hapus {
            color: #ef4444;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s;
        }
        .aksi-hapus:hover { color: #dc2626; text-decoration: underline; }
        
        .ringkasan {
            background: white;
            border-radius: 20px;
            padding: 25px 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .ringkasan h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .ringkasan-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .ringkasan-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            margin-top: 15px;
            border-top: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 18px;
            color: #1e293b;
        }
        .btn-checkout {
            background: #f97316;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(249,115,22,0.3);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            padding: 10px 24px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-secondary:hover { background: #e2e8f0; }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
        }
        .empty-cart i {
            font-size: 64px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        .empty-cart p {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .empty-cart a {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-block;
        }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 30px 0; margin-top: 60px; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 900px) {
            .cart-table th, .cart-table td { padding: 12px; }
            .product-cell { flex-direction: column; text-align: center; }
            .container { padding: 0 20px; }
        }
        @media (max-width: 768px) {
            .cart-table { display: block; overflow-x: auto; }
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
            <?php if(isset($_SESSION['user_id'])): ?>
                <span><i class="fas fa-user-circle"></i> Halo, <?= htmlspecialchars($nama_user) ?></span>
                <a href="keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $cart_count = $koneksi->query("SELECT SUM(jumlah) as total FROM keranjang WHERE id_user = $id_user")->fetch_assoc();
                    if($cart_count && $cart_count['total'] > 0) echo "<span class='cart-count'>{$cart_count['total']}</span>";
                    ?>
                </a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="../registrasi.php" class="btn-primary-nav">Daftar</a>
                <a href="../login.php" class="btn-outline">Masuk</a>
                <a href="keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="page-title">
        <h2><i class="fas fa-shopping-cart" style="margin-right: 12px; color: #f59e0b;"></i>Keranjang Belanja</h2>
        <a href="../belanja.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Lanjutkan Belanja</a>
    </div>

    <?php if(count($items) == 0): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Keranjang belanja Anda masih kosong</p>
            <a href="../belanja.php">Mulai Belanja Sekarang</a>
        </div>
    <?php else: ?>
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px;">
                <input type="checkbox" id="selectAllCheckbox" style="width: 18px; height: 18px;">
                Pilih Semua
            </label>
            <button type="button" id="deleteAllBtn" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 999px; font-size: 14px; font-weight: 600; cursor: pointer;">Hapus Terpilih</button>
        </div>

        <div class="cart-container">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;"></th>
                        <th>Produk</th>
                        <th>Harga Satuan</th>
                        <th>Kuantitas</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" class="item-checkbox" data-id="<?= $item['id_keranjang'] ?>" style="width: 18px; height: 18px;">
                        </td>
                        <td>
                            <div class="product-cell">
                                <div class="product-image">
                                    <?php if(!empty($item['gambar']) && file_exists("../assets/css/uploads/" . $item['gambar'])): ?>
                                        <img src="../assets/css/uploads/<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['judul']) ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4><?= htmlspecialchars($item['judul']) ?></h4>
                                    <p><?= htmlspecialchars($item['penulis'] ?: 'Penulis Terkenal') ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>
                            <div class="jumlah-wrapper">
                                <button type="button" class="kurang" data-id="<?= $item['id_keranjang'] ?>">-</button>
                                <input type="number" name="jumlah" value="<?= $item['jumlah'] ?>" min="1" class="jumlah-input" data-id="<?= $item['id_keranjang'] ?>">
                                <button type="button" class="tambah" data-id="<?= $item['id_keranjang'] ?>">+</button>
                            </div>
                        </td>
                        <td class="subtotal">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        <td>
                            <a href="?hapus=<?= $item['id_keranjang'] ?>" class="aksi-hapus" onclick="return confirm('Yakin ingin menghapus item ini?')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="ringkasan">
            <h3>Ringkasan Belanja</h3>
            <div class="ringkasan-row">
                <span>Total Harga (<?= count($items) ?> item)</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <div class="ringkasan-total">
                <span>Total Tagihan</span>
                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
            </div>
            <button type="button" class="btn-checkout" id="checkoutBtn">Checkout</button>
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

<script>
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const deleteAllBtn = document.getElementById('deleteAllBtn');

    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if(selectAllCheckbox) {
                selectAllCheckbox.checked = Array.from(itemCheckboxes).every(cb => cb.checked);
            }
        });
    });

    if(deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function() {
            const selectedIds = Array.from(itemCheckboxes).filter(cb => cb.checked).map(cb => cb.dataset.id);
            if(selectedIds.length === 0) {
                alert('Silakan pilih minimal 1 item untuk dihapus');
                return;
            }
            if(confirm('Yakin ingin menghapus item yang dipilih?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="hapus_terpilih" value="1">';
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'pilih[]';
                    input.value = id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function updateJumlah(idKeranjang, jumlah) {
        fetch('keranjang.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'update_jumlah=1&id_keranjang=' + idKeranjang + '&jumlah=' + jumlah
        }).then(() => location.reload());
    }

    document.querySelectorAll('.tambah').forEach(btn => {
        btn.addEventListener('click', function() {
            let input = this.parentElement.querySelector('.jumlah-input');
            let newVal = parseInt(input.value) + 1;
            input.value = newVal;
            updateJumlah(this.dataset.id, newVal);
        });
    });

    document.querySelectorAll('.kurang').forEach(btn => {
        btn.addEventListener('click', function() {
            let input = this.parentElement.querySelector('.jumlah-input');
            let newVal = Math.max(1, parseInt(input.value) - 1);
            input.value = newVal;
            updateJumlah(this.dataset.id, newVal);
        });
    });

    document.getElementById('checkoutBtn')?.addEventListener('click', function() {
        window.location.href = 'checkout.php';
    });
</script>

</body>
</html>
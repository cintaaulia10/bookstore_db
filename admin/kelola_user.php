<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/koneksi.php';

// Search - hanya untuk role user
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
if($search) {
    $user = $koneksi->query("SELECT id, username, email, role, created_at FROM users WHERE role = 'user' AND (username LIKE '%$search%' OR email LIKE '%$search%') ORDER BY id DESC");
} else {
    $user = $koneksi->query("SELECT id, username, email, role, created_at FROM users WHERE role = 'user' ORDER BY id DESC");
}

$nama_admin = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User -  PustakaStore Admin</title>
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
        
        .admin-info {
            display: flex; align-items: center; gap: 20px; color: white; margin-left: 15px;
        }
        .admin-name { font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .logout-link {
            color: #f87171; text-decoration: none; font-size: 14px; padding: 6px 12px;
            border-radius: 8px; transition: background 0.3s;
        }
        .logout-link:hover { background: rgba(248, 113, 113, 0.1); }
        
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { 
            width: 220px; 
            background: white; 
            padding: 30px 0; 
            position: sticky; 
            top: 77px; 
            height: calc(100vh - 77px);
            overflow-y: auto;
            border-right: 1px solid #e5e7eb;
        }
        .sidebar a { 
            display: block; 
            padding: 12px 24px; 
            text-decoration: none; 
            color: #334155; 
            margin-bottom: 4px; 
            font-weight: 500; 
            transition: all 0.3s ease; 
            font-size: 14px;
        }
        .sidebar a:hover { 
            background: #f1f5f9; 
            color: #0f172a; 
        }
        .sidebar a.active { 
            background: #f1f5f9; 
            color: #0f172a;
            border-left: 3px solid #3498db;
        }
        
        .content { flex: 1; padding: 30px 40px; }
        .content h2 { font-family: 'Poppins', sans-serif; font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .content > p { color: #64748b; margin-bottom: 30px; }
        
        .table-container { overflow-x: auto; background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8fafc; padding: 16px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; }
        .table td { padding: 16px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .table tr:hover { background: #f9fafb; }
        
        .role-badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        .role-user { background: #d1fae5; color: #059669; }
        
        .footer { background: #0f172a; color: #cbd5e1; padding: 20px 0; }
        .footer-bottom { text-align: center; font-size: 14px; }
        
        @media (max-width: 768px) { 
            .container { padding: 0 20px; } 
            .wrapper { flex-direction: column; } 
            .sidebar { width: 100%; height: auto; position: static; } 
            .content { padding: 20px; }
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

        <div class="search-wrapper">
            <form method="GET">
                <button type="submit"><i class="fas fa-search"></i></button>
                <input type="text" name="search" placeholder="Cari user..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <div class="admin-info">
            <div class="admin-name">
                <i class="fas fa-user-shield"></i> 
                <?= htmlspecialchars($nama_admin) ?>
            </div>
            <a href="../logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="wrapper">
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_buku.php">Kelola Buku</a>
        <a href="kelola_kategori.php">Kategori</a>
        <a href="kelola_user.php" class="active">User</a>
        <a href="kelola_pesanan.php">Pesanan</a>
    </div>

    <div class="content">
        <h2>Kelola User</h2>
        <p>Daftar semua member yang terdaftar di BookStore.</p>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($user && $user->num_rows > 0): ?>
                        <?php while($row = $user->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <span class="role-badge role-user">
                                    <i class="fas fa-user"></i> Member
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                <br><small><?= date('H:i', strtotime($row['created_at'])) ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px;">
                                <i class="fas fa-users" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
                                Tidak ada data member
                                <?php if($search): ?>
                                    <br><small>dengan kata kunci "<?= htmlspecialchars($search) ?>"</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
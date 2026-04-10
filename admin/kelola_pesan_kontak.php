<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config/koneksi.php';

$pesan = $koneksi->query("SELECT p.*, u.username, u.email FROM pesan_kontak p JOIN users u ON p.id_user = u.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Pesan Kontak</title>
</head>
<body>
    <h2>Pesan dari Pengguna</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Pengirim</th>
            <th>Email</th>
            <th>Subjek</th>
            <th>Pesan</th>
            <th>Tanggal</th>
        </tr>
        <?php while($row = $pesan->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['subjek'] ?></td>
            <td><?= $row['pesan'] ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
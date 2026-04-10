<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../config/koneksi.php';

$id_kategori = (int)$_GET['id'];

$query = $koneksi->query("SELECT id, judul, penulis, harga, stok FROM buku WHERE id_kategori = $id_kategori ORDER BY id ASC");
$buku = [];
while($row = $query->fetch_assoc()) {
    $buku[] = $row;
}

echo json_encode(['success' => true, 'buku' => $buku]);
?>
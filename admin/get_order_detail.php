<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../config/koneksi.php';

$id = (int)$_GET['id'];

// Ambil data pesanan (gunakan id_user)
$query_order = $koneksi->query("SELECT p.*, u.username, u.email FROM pesanan p JOIN users u ON p.id_user = u.id WHERE p.id = $id");
$order = $query_order->fetch_assoc();

if(!$order) {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
    exit();
}

// Ambil detail item pesanan (gunakan pesanan_id dan buku_id)
$query_items = $koneksi->query("SELECT d.*, b.judul, b.gambar FROM detail_pesanan d JOIN buku b ON d.buku_id = b.id WHERE d.pesanan_id = $id");
$items = [];
while($item = $query_items->fetch_assoc()) {
    $items[] = $item;
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
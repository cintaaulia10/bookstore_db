<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'bookstore_db';

$koneksi = mysqli_connect($host, $username, $password, $database);

if(!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($koneksi, "utf8");
?>
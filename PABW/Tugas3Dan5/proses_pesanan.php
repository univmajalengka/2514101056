<?php
// Tampilkan error saat development
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama_lengkap']);
    $email  = trim($_POST['email']);
    $telp   = trim($_POST['no_telepon']);
    $paket  = trim($_POST['paket_wisata']);
    $jumlah = (int)$_POST['jumlah_orang'];
    $tgl    = $_POST['tanggal_berangkat'];
    $catat  = trim($_POST['catatan']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($telp) || empty($paket) || $jumlah < 1 || empty($tgl)) {
        die("Data tidak lengkap. <a href='index.php'>Kembali</a>");
    }

    // Prepared statement (anti SQL injection)
    $stmt = $conn->prepare("INSERT INTO pemesanan (nama_lengkap, email, no_telepon, paket_wisata, jumlah_orang, tanggal_berangkat, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", $nama, $email, $telp, $paket, $jumlah, $tgl, $catat);

    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        header("Location: pesanan_sukses.php?id=" . $id);
        exit;
    } else {
        echo "Gagal menyimpan data: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>
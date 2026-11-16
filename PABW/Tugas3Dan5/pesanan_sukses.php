<?php
include 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Pesanan tidak ditemukan. <a href='index.php'>Kembali ke beranda</a>");
}

// Hitung total harga
$harga_paket = [
    'Jelajah Hutan Bambu' => 150000,
    'Tubing Sungai'       => 100000,
    'Camping Ground'      => 250000
];
$harga_satuan = $harga_paket[$data['paket_wisata']] ?? 0;
$total = $harga_satuan * $data['jumlah_orang'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil - Awi Lega</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f3; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .sukses-box {
            background: #fff;
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .icon { font-size: 4rem; margin-bottom: 15px; }
        h1 { color: #4a7c2e; margin-bottom: 10px; }
        .detail { text-align: left; background: #f4f9f0; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .detail p { margin: 8px 0; color: #333; }
        .detail span { font-weight: bold; color: #4a7c2e; }
        .total {
            background: #4a7c2e;
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-size: 1.2rem;
        }
        .total b { font-size: 1.5rem; }
        a.btn { display: inline-block; padding: 12px 25px; background: #4a7c2e; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 10px; }
        a.btn:hover { background: #38601f; }
    </style>
</head>
<body>
    <div class="sukses-box">
        <div class="icon">✅</div>
        <h1>Pemesanan Berhasil!</h1>
        <p>Terima kasih, <b><?= htmlspecialchars($data['nama_lengkap']) ?></b>. Pesanan Anda sudah kami terima.</p>
        <div class="detail">
            <p><strong>Kode Pesanan:</strong> <span>#AWL-<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?></span></p>
            <p><strong>Paket Wisata:</strong> <span><?= htmlspecialchars($data['paket_wisata']) ?></span></p>
            <p><strong>Jumlah Orang:</strong> <span><?= $data['jumlah_orang'] ?> orang</span></p>
            <p><strong>Tanggal Berangkat:</strong> <span><?= date('d F Y', strtotime($data['tanggal_berangkat'])) ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($data['email']) ?></span></p>
            <p><strong>No. Telepon:</strong> <span><?= htmlspecialchars($data['no_telepon']) ?></span></p>
        </div>
        <div class="total">
            Total Estimasi: <br><b>Rp <?= number_format($total, 0, ',', '.') ?></b>
        </div>
        <p style="font-size:0.85rem;color:#777;">Tim Awi Lega akan menghubungi Anda via WhatsApp dalam 1x24 jam.</p>
        <a href="index.php" class="btn">← Kembali ke Beranda</a>
    </div>
</body>
</html>
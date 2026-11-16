<?php
/**
 * ============================================================
 * JANUAR SHOP - SISTEM KASIR & HITUNG DISKON
 * ============================================================
 * Tugas Pertemuan 06-08
 * ============================================================
 */

session_start();

// ==================== KONFIGURASI ====================
define('NAMA_TOKO', 'Januar Shop');
define('ALAMAT_TOKO', 'Jl. Pahlawan No. 88, Bandung');
define('TELP_TOKO', '022-87654321');

define('BATAS_DISKON_10', 100000);
define('BATAS_DISKON_5', 50000);
define('PERSEN_DISKON_10', 0.10);
define('PERSEN_DISKON_5', 0.05);

define('PPN', 0.11);

// ==================== FUNGSI UTILITAS ====================

function rupiah($angka) {
    if (!is_numeric($angka)) return 'Rp. 0';
    return 'Rp. ' . number_format($angka, 0, ',', '.');
}

function validasiAngka($nilai) {
    if (!is_numeric($nilai)) return false;
    if ($nilai < 0) return false;
    if ($nilai > 1000000000000) return false;
    return true;
}

function bersihkan($input) {
    return htmlspecialchars(trim(stripslashes($input)));
}

function getPersenDiskon($total) {
    if ($total >= BATAS_DISKON_10) return PERSEN_DISKON_10;
    if ($total >= BATAS_DISKON_5) return PERSEN_DISKON_5;
    return 0;
}

function getLabelDiskon($total) {
    if ($total >= BATAS_DISKON_10) return 'Diskon 10%';
    if ($total >= BATAS_DISKON_5) return 'Diskon 5%';
    return 'Tanpa Diskon';
}

function getBadgeDiskon($total) {
    if ($total >= BATAS_DISKON_10) return 'badge-gold';
    if ($total >= BATAS_DISKON_5) return 'badge-silver';
    return 'badge-gray';
}

// ==================== PROSEDUR UTAMA ====================

function hitungDiskon($totalBelanja) {
    $diskon = 0;
    if (!validasiAngka($totalBelanja)) return 0;

    if ($totalBelanja >= BATAS_DISKON_10) {
        $diskon = PERSEN_DISKON_10 * $totalBelanja;
    } elseif ($totalBelanja >= BATAS_DISKON_5) {
        $diskon = PERSEN_DISKON_5 * $totalBelanja;
    }

    return $diskon;
}

function hitungTotal($totalBelanja, $diskon, $denganPpn = false) {
    $subtotal = $totalBelanja - $diskon;
    $pajak = $denganPpn ? PPN * $subtotal : 0;
    return [
        'total_belanja' => $totalBelanja,
        'diskon' => $diskon,
        'subtotal' => $subtotal,
        'pajak' => $pajak,
        'total_bayar' => $subtotal + $pajak
    ];
}

// ==================== SISTEM VOUCHER ====================

function daftarVoucher() {
    return [
        'HEMAT10' => ['tipe' => 'persen', 'nilai' => 0.10, 'ket' => 'Diskon tambahan 10%'],
        'HEMAT5' => ['tipe' => 'persen', 'nilai' => 0.05, 'ket' => 'Diskon tambahan 5%'],
        'NEWUSER' => ['tipe' => 'nominal', 'nilai' => 15000, 'ket' => 'Potongan Rp 15.000'],
        'ONGKIR' => ['tipe' => 'nominal', 'nilai' => 20000, 'ket' => 'Potongan Rp 20.000'],
        'JANUAR50' => ['tipe' => 'nominal', 'nilai' => 50000, 'ket' => 'Potongan Rp 50.000']
    ];
}

function hitungVoucher($kode, $subtotal) {
    $daftar = daftarVoucher();
    $kode = strtoupper(trim($kode));

    if (!array_key_exists($kode, $daftar)) {
        return ['valid' => false, 'potongan' => 0, 'ket' => 'Kode voucher tidak valid'];
    }

    $v = $daftar[$kode];
    if ($v['tipe'] === 'persen') {
        $potongan = $v['nilai'] * $subtotal;
    } else {
        $potongan = min($v['nilai'], $subtotal);
    }

    return ['valid' => true, 'potongan' => $potongan, 'ket' => $v['ket']];
}

// ==================== RIWAYAT TRANSAKSI ====================

function inisialisasiRiwayat() {
    if (!isset($_SESSION['riwayat'])) {
        $_SESSION['riwayat'] = [];
    }
}

function tambahRiwayat($data) {
    inisialisasiRiwayat();
    $data['id'] = count($_SESSION['riwayat']) + 1;
    $data['waktu'] = date('H:i:s');
    $_SESSION['riwayat'][] = $data;
}

function getRiwayat() {
    inisialisasiRiwayat();
    return $_SESSION['riwayat'];
}

function hapusRiwayat() {
    $_SESSION['riwayat'] = [];
}

function getStatistik() {
    $riwayat = getRiwayat();
    $stats = [
        'jumlah' => count($riwayat),
        'total_belanja' => 0,
        'total_diskon' => 0,
        'total_bayar' => 0,
        'total_voucher' => 0
    ];

    foreach ($riwayat as $r) {
        $stats['total_belanja'] += $r['total_belanja'];
        $stats['total_diskon'] += $r['diskon'];
        $stats['total_bayar'] += $r['total_bayar'];
        $stats['total_voucher'] += isset($r['voucher']) ? $r['voucher'] : 0;
    }

    return $stats;
}

// ==================== PROSES FORM ====================

inisialisasiRiwayat();

$pesan = '';
$tipePesan = '';
$hasil = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['aksi']) && $_POST['aksi'] === 'hapus_riwayat') {
        hapusRiwayat();
        $pesan = 'Riwayat transaksi berhasil dihapus.';
        $tipePesan = 'success';
    } elseif (isset($_POST['aksi']) && $_POST['aksi'] === 'reset') {
        $pesan = 'Form berhasil direset.';
        $tipePesan = 'info';
    } else {
        $inputTotal = bersihkan($_POST['total_belanja'] ?? '');
        $kodeVoucher = bersihkan($_POST['voucher'] ?? '');
        $pakaiPpn = isset($_POST['pakai_ppn']);
        $simpanRiwayat = isset($_POST['simpan_riwayat']);

        if (empty($inputTotal)) {
            $pesan = 'Total belanja tidak boleh kosong.';
            $tipePesan = 'error';
        } elseif (!validasiAngka($inputTotal)) {
            $pesan = 'Total belanja harus berupa angka positif yang valid.';
            $tipePesan = 'error';
        } else {
            $totalBelanja = (float) $inputTotal;
            $diskon = hitungDiskon($totalBelanja);
            $hasil = hitungTotal($totalBelanja, $diskon, $pakaiPpn);

            if (!empty($kodeVoucher)) {
                $voucherInfo = hitungVoucher($kodeVoucher, $hasil['subtotal']);
                if ($voucherInfo['valid']) {
                    $hasil['voucher'] = $voucherInfo['potongan'];
                    $hasil['total_bayar'] -= $voucherInfo['potongan'];
                    if ($hasil['total_bayar'] < 0) $hasil['total_bayar'] = 0;
                    $pesan = 'Voucher ' . strtoupper($kodeVoucher) . ' berhasil digunakan!';
                    $tipePesan = 'success';
                } else {
                    $pesan = 'Voucher tidak valid, transaksi tetap diproses.';
                    $tipePesan = 'warning';
                }
            } else {
                $pesan = 'Transaksi berhasil dihitung.';
                $tipePesan = 'success';
            }

            if ($simpanRiwayat) {
                tambahRiwayat([
                    'total_belanja' => $hasil['total_belanja'],
                    'diskon' => $hasil['diskon'],
                    'subtotal' => $hasil['subtotal'],
                    'pajak' => $hasil['pajak'],
                    'voucher' => isset($hasil['voucher']) ? $hasil['voucher'] : 0,
                    'total_bayar' => $hasil['total_bayar'],
                    'ppn' => $pakaiPpn
                ]);
            }
        }
    }
}

$riwayat = getRiwayat();
$statistik = getStatistik();
$voucherTersedia = daftarVoucher();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Januar Shop - Sistem Kasir</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
        --primary: #e11d48;
        --primary-dark: #be123c;
        --primary-light: #fecdd3;
        --accent: #f59e0b;
        --success: #16a34a;
        --danger: #dc2626;
        --dark: #1c1917;
        --gray: #78716c;
        --light: #fafaf9;
        --white: #ffffff;
        --cream: #fef3c7;
    }

    body {
        font-family: 'Georgia', 'Times New Roman', serif;
        background: #1c1917;
        background-image:
            radial-gradient(circle at 20% 30%, rgba(225,29,72,0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(245,158,11,0.12) 0%, transparent 50%);
        min-height: 100vh;
        padding: 20px;
        color: var(--dark);
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .header {
        text-align: center;
        color: white;
        margin-bottom: 30px;
        padding: 30px 20px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border-radius: 4px;
        border-bottom: 6px solid var(--accent);
        position: relative;
        overflow: hidden;
    }

    .header::before {
        content: '★';
        position: absolute;
        top: -20px;
        right: 20px;
        font-size: 8rem;
        opacity: 0.08;
    }

    .header::after {
        content: '★';
        position: absolute;
        bottom: -30px;
        left: 20px;
        font-size: 6rem;
        opacity: 0.08;
    }

    .header h1 {
        font-size: 2.5rem;
        margin-bottom: 8px;
        letter-spacing: 3px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .header p {
        opacity: 0.85;
        font-size: 0.9rem;
        letter-spacing: 1px;
        font-style: italic;
    }

    .header .divider {
        width: 60px;
        height: 2px;
        background: var(--accent);
        margin: 12px auto;
    }

    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .grid { grid-template-columns: 1fr; }
        .header h1 { font-size: 1.6rem; }
    }

    .card {
        background: var(--white);
        border-radius: 4px;
        padding: 28px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.4);
        border-top: 4px solid var(--primary);
        position: relative;
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary));
    }

    .card h2 {
        font-size: 1.25rem;
        color: var(--primary-dark);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--primary-light);
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: 1px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--dark);
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .form-group input[type="number"],
    .form-group input[type="text"] {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e7e5e4;
        border-radius: 4px;
        font-size: 1rem;
        transition: all 0.3s;
        font-family: inherit;
        background: var(--light);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(225,29,72,0.12);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding: 8px 12px;
        background: var(--light);
        border-radius: 4px;
        border-left: 3px solid var(--accent);
    }

    .checkbox-group input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .checkbox-group label {
        cursor: pointer;
        font-size: 0.9rem;
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
    }

    .btn-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(225,29,72,0.45);
    }

    .btn-secondary {
        background: var(--light);
        color: var(--dark);
        border: 2px solid #e7e5e4;
    }

    .btn-secondary:hover {
        background: #e7e5e4;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .alert {
        padding: 14px 18px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Segoe UI', sans-serif;
        border-left: 5px solid;
    }

    .alert-success { background: #dcfce7; color: #14532d; border-color: var(--success); }
    .alert-error { background: #fee2e2; color: #7f1d1d; border-color: var(--danger); }
    .alert-warning { background: #fef3c7; color: #78350f; border-color: var(--accent); }
    .alert-info { background: #fef2f2; color: #881337; border-color: var(--primary); }

    .hasil-box {
        background: linear-gradient(135deg, var(--light), #f5f5f4);
        border-radius: 4px;
        padding: 20px;
        border: 2px dashed var(--primary);
    }

    .hasil-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e7e5e4;
        font-size: 0.95rem;
        font-family: 'Segoe UI', sans-serif;
    }

    .hasil-row:last-child { border-bottom: none; }

    .hasil-row.total {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-dark);
        border-top: 3px double var(--primary);
        margin-top: 10px;
        padding-top: 15px;
    }

    .hasil-row .label { color: var(--gray); }
    .hasil-row .value { font-weight: 700; }
    .hasil-row.total .value { color: var(--success); }

    .badge {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 3px;
        font-size: 0.75rem;
        font-weight: 700;
        font-family: 'Segoe UI', sans-serif;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .badge-gold { background: var(--cream); color: #78350f; border: 1px solid var(--accent); }
    .badge-silver { background: #f5f5f4; color: #57534e; border: 1px solid #a8a29e; }
    .badge-gray { background: #fafaf9; color: var(--gray); border: 1px solid #e7e5e4; }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 18px 15px;
        border-radius: 4px;
        text-align: center;
        border-bottom: 3px solid var(--accent);
    }

    .stat-card.green { background: linear-gradient(135deg, #16a34a, #15803d); }
    .stat-card.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-card.red { background: linear-gradient(135deg, #dc2626, #b91c1c); }

    .stat-card .stat-value {
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 4px;
        font-family: 'Segoe UI', sans-serif;
    }

    .stat-card .stat-label {
        font-size: 0.7rem;
        opacity: 0.9;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 4px;
        border: 1px solid #e7e5e4;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        font-family: 'Segoe UI', sans-serif;
    }

    table th {
        background: var(--dark);
        color: white;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    table td {
        padding: 10px;
        border-bottom: 1px solid #e7e5e4;
    }

    table tr:hover { background: #fef2f2; }
    table tr:nth-child(even) { background: var(--light); }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray);
        font-family: 'Segoe UI', sans-serif;
    }

    .empty-state .icon {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .voucher-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .voucher-item {
        background: linear-gradient(135deg, var(--cream), #fde68a);
        border: 1px dashed var(--accent);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Segoe UI', sans-serif;
    }

    .voucher-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(245,158,11,0.4);
    }

    .voucher-item strong { color: #78350f; display: block; }
    .voucher-item span { color: var(--gray); font-size: 0.68rem; }

    .footer {
        text-align: center;
        color: #a8a29e;
        margin-top: 30px;
        font-size: 0.85rem;
        font-family: 'Segoe UI', sans-serif;
        padding: 20px;
        border-top: 1px solid #292524;
    }

    .section-full { margin-top: 20px; }

    .struck {
        background: #fffef8;
        border-radius: 4px;
        padding: 22px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        border: 2px solid var(--dark);
        box-shadow: inset 0 0 0 4px white, inset 0 0 0 5px #e7e5e4;
    }

    .struck-header {
        text-align: center;
        border-bottom: 2px dashed var(--dark);
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .struck-header h3 {
        font-size: 1.2rem;
        letter-spacing: 3px;
        text-transform: uppercase;
    }
    .struck-header p { font-size: 0.72rem; color: var(--gray); }

    .struck-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
    }

    .struck-total {
        border-top: 2px dashed var(--dark);
        margin-top: 8px;
        padding-top: 8px;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--primary-dark);
    }

    .struck-footer {
        text-align: center;
        border-top: 2px dashed var(--dark);
        margin-top: 10px;
        padding-top: 10px;
        font-size: 0.72rem;
        color: var(--gray);
    }
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Januar Shop</h1>
        <div class="divider"></div>
        <p><?= ALAMAT_TOKO ?> &bull; <?= TELP_TOKO ?></p>
    </div>

    <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipePesan ?>">
        <?= $pesan ?>
    </div>
    <?php endif; ?>

    <div class="grid">

        <!-- FORM INPUT -->
        <div class="card">
            <h2>Input Transaksi</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="total_belanja">Total Belanja (Rp)</label>
                    <input type="number" id="total_belanja" name="total_belanja" 
                           placeholder="Contoh: 120000" min="0" step="1"
                           value="<?= isset($_POST['total_belanja']) ? bersihkan($_POST['total_belanja']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="voucher">Kode Voucher (Opsional)</label>
                    <input type="text" id="voucher" name="voucher" 
                           placeholder="Contoh: HEMAT10"
                           value="<?= isset($_POST['voucher']) ? bersihkan($_POST['voucher']) : '' ?>">
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="pakai_ppn" name="pakai_ppn" 
                           <?= isset($_POST['pakai_ppn']) ? 'checked' : '' ?>>
                    <label for="pakai_ppn">Hitung PPN 11%</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="simpan_riwayat" name="simpan_riwayat" checked>
                    <label for="simpan_riwayat">Simpan ke Riwayat</label>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Hitung Diskon</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>

            <div style="margin-top:20px;">
                <label style="font-weight:700; font-size:0.8rem; color:var(--gray); letter-spacing:1px; text-transform:uppercase;">Voucher Tersedia:</label>
                <div class="voucher-list">
                    <?php foreach ($voucherTersedia as $kode => $v): ?>
                    <div class="voucher-item" onclick="document.getElementById('voucher').value='<?= $kode ?>'">
                        <strong><?= $kode ?></strong>
                        <span><?= $v['ket'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- HASIL PERHITUNGAN -->
        <div class="card">
            <h2>Hasil Perhitungan</h2>

            <?php if ($hasil): ?>
            <div style="margin-bottom:15px;">
                <span class="badge <?= getBadgeDiskon($hasil['total_belanja']) ?>">
                    <?= getLabelDiskon($hasil['total_belanja']) ?>
                </span>
            </div>

            <div class="hasil-box">
                <div class="hasil-row">
                    <span class="label">Total Belanja</span>
                    <span class="value"><?= rupiah($hasil['total_belanja']) ?></span>
                </div>
                <div class="hasil-row">
                    <span class="label">Diskon (<?= number_format(getPersenDiskon($hasil['total_belanja']) * 100, 0) ?>%)</span>
                    <span class="value" style="color:var(--success);">- <?= rupiah($hasil['diskon']) ?></span>
                </div>
                <div class="hasil-row">
                    <span class="label">Subtotal</span>
                    <span class="value"><?= rupiah($hasil['subtotal']) ?></span>
                </div>
                <?php if ($hasil['pajak'] > 0): ?>
                <div class="hasil-row">
                    <span class="label">PPN (11%)</span>
                    <span class="value">+ <?= rupiah($hasil['pajak']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($hasil['voucher']) && $hasil['voucher'] > 0): ?>
                <div class="hasil-row">
                    <span class="label">Voucher</span>
                    <span class="value" style="color:var(--success);">- <?= rupiah($hasil['voucher']) ?></span>
                </div>
                <?php endif; ?>
                <div class="hasil-row total">
                    <span class="label">TOTAL BAYAR</span>
                    <span class="value"><?= rupiah($hasil['total_bayar']) ?></span>
                </div>
            </div>

            <div style="margin-top:15px;">
                <div class="struck">
                    <div class="struck-header">
                        <h3>Januar Shop</h3>
                        <p><?= ALAMAT_TOKO ?></p>
                    </div>
                    <div class="struck-row">
                        <span>Total Belanja</span>
                        <span><?= rupiah($hasil['total_belanja']) ?></span>
                    </div>
                    <div class="struck-row">
                        <span>Diskon</span>
                        <span>-<?= rupiah($hasil['diskon']) ?></span>
                    </div>
                    <div class="struck-row">
                        <span>Subtotal</span>
                        <span><?= rupiah($hasil['subtotal']) ?></span>
                    </div>
                    <?php if ($hasil['pajak'] > 0): ?>
                    <div class="struck-row">
                        <span>PPN 11%</span>
                        <span><?= rupiah($hasil['pajak']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($hasil['voucher']) && $hasil['voucher'] > 0): ?>
                    <div class="struck-row">
                        <span>Voucher</span>
                        <span>-<?= rupiah($hasil['voucher']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="struck-row struck-total">
                        <span>TOTAL</span>
                        <span><?= rupiah($hasil['total_bayar']) ?></span>
                    </div>
                    <div class="struck-footer">
                        <p>Terima Kasih Telah Berbelanja</p>
                        <p><?= date('d/m/Y H:i:s') ?></p>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#9998;</div>
                <p>Belum ada transaksi.</p>
                <p style="font-size:0.85rem; margin-top:5px;">Masukkan total belanja untuk melihat hasil perhitungan.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- STATISTIK -->
    <?php if ($statistik['jumlah'] > 0): ?>
    <div class="card section-full">
        <h2>Statistik Transaksi</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $statistik['jumlah'] ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value" style="font-size:0.95rem;"><?= rupiah($statistik['total_belanja']) ?></div>
                <div class="stat-label">Total Belanja</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value" style="font-size:0.95rem;"><?= rupiah($statistik['total_diskon']) ?></div>
                <div class="stat-label">Total Diskon</div>
            </div>
            <div class="stat-card red">
                <div class="stat-value" style="font-size:0.95rem;"><?= rupiah($statistik['total_bayar']) ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- RIWAYAT TRANSAKSI -->
    <div class="card section-full">
        <h2 style="justify-content:space-between;">
            <span>Riwayat Transaksi</span>
            <?php if (!empty($riwayat)): ?>
            <form method="POST" style="margin:0;" onsubmit="return confirm('Hapus semua riwayat?')">
                <input type="hidden" name="aksi" value="hapus_riwayat">
                <button type="submit" class="btn btn-danger" style="padding:8px 16px; font-size:0.75rem;">
                    Hapus Semua
                </button>
            </form>
            <?php endif; ?>
        </h2>

        <?php if (empty($riwayat)): ?>
        <div class="empty-state">
            <div class="icon">&#128193;</div>
            <p>Belum ada riwayat transaksi.</p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Total Belanja</th>
                        <th>Diskon</th>
                        <th>PPN</th>
                        <th>Voucher</th>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($riwayat) as $r): ?>
                    <tr>
                        <td><strong>#<?= $r['id'] ?></strong></td>
                        <td><?= $r['waktu'] ?></td>
                        <td><?= rupiah($r['total_belanja']) ?></td>
                        <td style="color:var(--success);">-<?= rupiah($r['diskon']) ?></td>
                        <td><?= $r['pajak'] > 0 ? rupiah($r['pajak']) : '-' ?></td>
                        <td><?= isset($r['voucher']) && $r['voucher'] > 0 ? '-' . rupiah($r['voucher']) : '-' ?></td>
                        <td><strong><?= rupiah($r['total_bayar']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Januar Shop &bull; Sistem Kasir &amp; Hitung Diskon</p>
        <p>Tugas Pertemuan 06-08 - Pemrograman PHP</p>
    </div>

</div>
</body>
</html>
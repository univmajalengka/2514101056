<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awi Lega - Wisata Alam Bambu & Sungai</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        /* ===== NAVBAR ===== */
        nav {
            background: #fff;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        nav .logo { display: flex; align-items: center; gap: 10px; font-weight: bold; font-size: 1.3rem; color: #4a7c2e; }
        nav .logo img { width: 40px; height: 40px; object-fit: contain; }
        nav ul { display: flex; list-style: none; gap: 25px; }
        nav ul li a { text-decoration: none; color: #333; font-weight: 500; }
        nav ul li a:hover { color: #4a7c2e; }

        /* ===== HERO ===== */
        .hero {
            height: 500px;
            background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)),
                        url('assets/images/hero-bg.jpg') center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .hero h1 { font-size: 3rem; margin-bottom: 15px; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
        .hero p { font-size: 1.2rem; text-shadow: 1px 1px 5px rgba(0,0,0,0.7); margin-bottom: 25px; }
        .hero .btn-hero {
            padding: 12px 30px;
            background: #4a7c2e;
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s;
        }
        .hero .btn-hero:hover { background: #38601f; transform: translateY(-3px); }

        /* ===== TENTANG ===== */
        .tentang {
            padding: 60px 50px;
            background: #f4f9f0;
            text-align: center;
        }
        .tentang h2 { color: #4a7c2e; font-size: 2rem; margin-bottom: 20px; }
        .tentang p { color: #555; max-width: 700px; margin: 0 auto; line-height: 1.8; font-size: 1.05rem; }

        /* ===== PAKET WISATA ===== */
        .destinasi-section { padding: 60px 50px; background: #fff; }
        .destinasi-section h2 { text-align: center; color: #4a7c2e; margin-bottom: 40px; font-size: 2rem; }
        .card-container { display: flex; gap: 25px; justify-content: center; flex-wrap: wrap; }
        .card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            width: 320px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover { transform: translateY(-10px); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card-body { padding: 20px; }
        .card-body h3 { color: #4a7c2e; margin-bottom: 10px; }
        .card-body p { color: #666; font-size: 0.9rem; margin-bottom: 10px; line-height: 1.6; }
        .card-body .harga { color: #e67e22; font-weight: bold; font-size: 1.1rem; }

        /* ===== FORM ===== */
        .form-section {
            padding: 60px 20px;
            background: linear-gradient(135deg, #4a7c2e, #6ba63f);
            display: flex;
            justify-content: center;
        }
        .form-box {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-box h2 { color: #4a7c2e; text-align: center; margin-bottom: 10px; }
        .form-box .subtitle { text-align: center; color: #777; margin-bottom: 25px; font-size: 0.95rem; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #4a7c2e;
            outline: none;
            box-shadow: 0 0 5px rgba(74,124,46,0.3);
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #4a7c2e;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #38601f; }

        footer {
            background: #2d4a1a;
            color: #ccc;
            text-align: center;
            padding: 25px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            nav { padding: 15px 20px; }
            nav ul { display: none; }
            .hero h1 { font-size: 2rem; }
            .destinasi-section, .tentang { padding: 40px 20px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <img src="assets/images/logo.png" alt="Logo Awi Lega">
        <span>Awi Lega</span>
    </div>
    <ul>
        <li><a href="#tentang">Tentang</a></li>
        <li><a href="#paket">Paket Wisata</a></li>
        <li><a href="#pesan">Pesan</a></li>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <h1>Selamat Datang di Awi Lega</h1>
    <p>Wisata alam bambu & sungai yang menyegarkan jiwa</p>
    <a href="#pesan" class="btn-hero">Pesan Sekarang 🌿</a>
</section>

<!-- TENTANG -->
<section class="tentang" id="tentang">
    <h2>Tentang Awi Lega</h2>
    <p>
        <b>Awi Lega</b> adalah destinasi wisata alam yang menyajikan keindahan hutan bambu,
        aliran sungai jernih, dan udara pegunungan yang sejuk. Cocok untuk keluarga,
        rombongan sekolah, maupun gathering kantor. Nikmati pengalaman camping,
        tubing, dan jelajah hutan bambu bersama pemandu lokal berpengalaman.
    </p>
</section>

<!-- PAKET WISATA -->
<section class="destinasi-section" id="paket">
    <h2>Paket Wisata Kami</h2>
    <div class="card-container">
        <div class="card">
            <img src="assets/images/destinasi-bambu.jpg" alt="Jelajah Hutan Bambu">
            <div class="card-body">
                <h3>Jelajah Hutan Bambu</h3>
                <p>Susur hutan bambu dengan pemandu lokal, spot foto instagramable, dan edukasi budaya bambu.</p>
                <div class="harga">Rp 150.000 / orang</div>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/destinasi-sungai.jpg" alt="Tubing Sungai">
            <div class="card-body">
                <h3>Tubing Sungai</h3>
                <p>Arung jeram santai dengan ban pelampung menyusuri sungai jernih, aman untuk pemula.</p>
                <div class="harga">Rp 100.000 / orang</div>
            </div>
        </div>
        <div class="card">
            <img src="assets/images/destinasi-camping.jpg" alt="Camping Ground">
            <div class="card-body">
                <h3>Camping Ground</h3>
                <p>Bermalam di area camping Awi Lega dengan tenda, api unggun, dan sarapan pagi.</p>
                <div class="harga">Rp 250.000 / orang</div>
            </div>
        </div>
    </div>
</section>

<!-- FORM PEMESANAN -->
<section class="form-section" id="pesan">
    <div class="form-box">
        <h2>📝 Formulir Pemesanan</h2>
        <p class="subtitle">Isi data di bawah untuk memesan paket wisata Awi Lega</p>
        <form action="proses_pesanan.php" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Nama kamu" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@contoh.com" required>
            </div>
            <div class="form-group">
                <label>No. Telepon / WhatsApp</label>
                <input type="text" name="no_telepon" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label>Pilih Paket Wisata</label>
                <select name="paket_wisata" required>
                    <option value="">-- Pilih Paket --</option>
                    <option value="Jelajah Hutan Bambu">Jelajah Hutan Bambu - Rp 150.000</option>
                    <option value="Tubing Sungai">Tubing Sungai - Rp 100.000</option>
                    <option value="Camping Ground">Camping Ground - Rp 250.000</option>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Orang</label>
                <input type="number" name="jumlah_orang" min="1" placeholder="contoh: 4" required>
            </div>
            <div class="form-group">
                <label>Tanggal Berangkat</label>
                <input type="date" name="tanggal_berangkat" required>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" placeholder="Permintaan khusus, misalnya: butuh vegetarian, ada anak kecil, dll."></textarea>
            </div>
            <button type="submit" class="btn-submit">Pesan Sekarang 🚀</button>
        </form>
    </div>
</section>

<footer>
    &copy; 2025 <b>Awi Lega</b> - Wisata Alam Bambu & Sungai. All rights reserved.
</footer>

</body>
</html>
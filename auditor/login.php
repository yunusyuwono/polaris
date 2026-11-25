<?php
$errorMessage = '';
if(isset($_POST['cari']))
{
    $us = trim($_POST['username'] ?? '');
    $pw = trim($_POST['password'] ?? '');
    require_once "../koneksi.php";
    $pw_md5 = md5($pw);
    $query = mysqli_query($kon, "SELECT * FROM user WHERE nip='".mysqli_real_escape_string($kon, $us)."' AND password='$pw_md5'");

    if(mysqli_num_rows($query) > 0)
    {
        $pegawai = mysqli_fetch_array($query);
        session_start();
        $_SESSION['us'] = $pegawai['nip'] ?? $us;
        $_SESSION['id_pegawai'] = $pegawai['id'] ?? '';
        $_SESSION['nama'] = $pegawai['nama'] ?? '';
        header("location:./");
        exit;
    }
    else
    {
        $errorMessage = 'NIP atau password tidak valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Auditor POLARIS</title>
        <link rel="icon" href="../assets/POLARIS-HD.png">
        <link rel="stylesheet" href="../assets/css/bootstrap.css">
        <link rel="stylesheet" href="../assets/css/app.css">
        <link rel="stylesheet" href="../assets/css/pages/auth.css">
        <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
        <style>
            body { min-height: 100vh; background: linear-gradient(135deg, #0f172a 0%, #1f2937 56%, #2563eb 100%); }
            #auth-right { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
            .auth-box { max-width: 460px; width: 100%; border-radius: 1.25rem; box-shadow: 0 26px 70px rgba(0,0,0,.24); overflow: hidden; }
            .auth-brand { font-size: 2.25rem; letter-spacing: 0.12em; font-weight: 800; }
            .auth-title { font-size: 1.75rem; font-weight: 700; color: #fff; }
            .auth-copy { color: rgba(255,255,255,.78); max-width: 460px; }
            .auditor-badge { background: rgba(37,99,235,.12); color: #1d4ed8; border: 1px solid rgba(37,99,235,.18); }
            .auth-box .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.24); border-color: #2563eb; }
            .auth-box .input-group-text { background: #f8fafc; border-right: 0; color: #1d4ed8; }
            .auth-box .form-control { border-left: 0; }
            .auth-box .card-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; }
            .btn-auditor { background: #2563eb; border-color: #2563eb; color: #fff; }
            .btn-auditor:hover, .btn-auditor:focus { background: #1d4ed8; border-color: #1d4ed8; color: #fff; }
            .auth-panel-icon {
                width: 48px;
                height: 48px;
                border-radius: .85rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,.12);
                border: 1px solid rgba(255,255,255,.18);
                margin-bottom: 1.25rem;
            }
        </style>
    </head>
    <body>
        <div id="auth">
            <div class="row h-100 gx-0">
                <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center">
                    <div class="text-white px-4 py-5">
                        <div class="auth-logo mb-4 d-flex align-items-center" style="gap: 15px;">
                            <img src="../assets/POLARIS-HD.png" alt="POLARIS" style="height: 60px;">
                            <img src="../poltekkes.png" alt="Poltekkes" style="height: 60px;">
                        </div>
                        <h1 class="auth-brand text-white mb-2">POLARIS</h1>
                        <h2 class="auth-title mb-3">POLKESLU Penjaminan Mutu Terintegrasi</h2>
                        <p class="auth-copy mb-0">Ruang Kerja Auditor. Kelola pra audit, audit lapangan, evaluasi temuan, dan pelaporan mutu internal Poltekkes Kemenkes Bengkulu dalam satu portal.</p>
                    </div>
                </div>
                <div class="col-lg-6" id="auth-right">
                    <div class="card auth-box">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <span class="badge auditor-badge px-3 py-2 mb-3">Auditor Login</span>
                                <h3 class="mb-2">Masuk ke POLARIS</h3>
                                <p class="text-muted mb-0">Gunakan akun NIP dan password untuk mengakses panel auditor.</p>
                            </div>
                            <?php if(!empty($errorMessage)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
                            <?php endif; ?>
                            <form method="post" autocomplete="off" onsubmit="showloader()">
                                <div class="mb-3">
                                    <label class="form-label">NIP / Username</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                        <input type="text" name="username" class="form-control" placeholder="NIP atau username" autofocus required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                                    </div>
                                </div>
                                <button name="cari" class="btn btn-auditor btn-lg w-100" type="submit">
                                    <span id="loginText">Masuk Sekarang</span>
                                    <span id="loader" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </form>
                        </div>
                        <div class="card-footer text-center text-muted">
                            POLARIS &copy; <?= date('Y'); ?> - Poltekkes Kemenkes Bengkulu
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function showloader(){
                var loader = document.getElementById('loader');
                var text = document.getElementById('loginText');
                if(loader) loader.classList.remove('d-none');
                if(text) text.textContent = 'Memeriksa...';
            }
        </script>
    </body>
</html>

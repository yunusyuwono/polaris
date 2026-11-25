
<?php
$errorMessage = '';
if (isset($_POST['cari'])) {
    $us = addslashes($_POST['username']);
    $pw = md5(addslashes($_POST['password']));
    include "koneksi.php";
    $query = mysqli_query($kon, "select * from admin where username='$us' and password='$pw'");
    if (mysqli_num_rows($query) > 0) {
        $ga = mysqli_fetch_array($query);
        session_start();
        $_SESSION['id_pegawai'] = $ga['id'];
        $_SESSION['nama'] = $ga['nama'];
        $_SESSION['level'] = 'Admin';
        header("location:./");
        exit;
    } else {
        $errorMessage = 'Username atau password tidak valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Admin POLARIS</title>
        <link rel="icon" href="assets/POLARIS-HD.png">
        <link rel="stylesheet" href="assets/css/bootstrap.css">
        <link rel="stylesheet" href="assets/css/app.css">
        <link rel="stylesheet" href="assets/css/pages/auth.css">
        <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
        <style>
            body { min-height: 100vh; background: linear-gradient(135deg, #0d3c75 0%, #2574d5 100%); }
            #auth-right { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
            .auth-box { max-width: 460px; width: 100%; border-radius: 1.25rem; box-shadow: 0 26px 70px rgba(0,0,0,.22); }
            .auth-brand { font-size: 2.25rem; letter-spacing: 0.12em; font: 700 2.25rem/1 'Poppins', sans-serif; }
            .auth-box .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(49,132,253,0.28); border-color: #3174ff; }
            .auth-box .input-group-text { background: #f8f9ff; border-right: 0; }
            .auth-box .form-control { border-left: 0; }
            .auth-box .card-footer { background: #f8f9ff; border-top: 1px solid #e1e7f5; }
            .auth-title { font-size: 1.75rem; font-weight: 600; color: #fff; }
        </style>
    </head>
    <body>
        <div id="auth">
            <div class="row h-100 gx-0">
                <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center" id="auth-left">
                    <div class="text-white px-4 py-5">
                        <div class="auth-logo mb-4 d-flex align-items-center" style="gap: 15px;">
                            <img src="assets/POLARIS-HD.png" alt="POLARIS" style="height: 60px;">
                            <img src="poltekkes.png" alt="Poltekkes" style="height: 60px;">
                        </div>
                        <h2 class="auth-title" style="font-family: 'Poppins', sans-serif;">POLARIS</h2>
                        <p class="auth-subtitle" style="font-family: 'Poppins', sans-serif;">POLKESLU Penjaminan Mutu Terintegrasi<br><b>Poltekkes Kemenkes Bengkulu</b></p>
                    </div>
                </div>
                <div class="col-lg-6" id="auth-right">
                    <div class="card auth-box">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <span class="badge bg-primary px-3 py-2 mb-3">Admin Login</span>
                                <h3 class="mb-2">Selamat Datang</h3>
                                <p class="text-muted mb-0">Gunakan akun Admin untuk mengakses panel pengelolaan POLARIS.</p>
                            </div>
                            <?php if (!empty($errorMessage)): ?>
                                <div class="alert alert-danger"><?= $errorMessage ?></div>
                            <?php endif; ?>
                            <form method="post" autocomplete="off">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="username" class="form-control" placeholder="Username" autofocus required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                                    </div>
                                </div>
                                <button type="submit" name="cari" class="btn btn-primary btn-lg w-100">Masuk Sekarang</button>
                            </form>
                        </div>
                        <div class="card-footer text-center text-muted">
                            POLARIS &copy; <?= date('Y'); ?> - Poltekkes Kemenkes Bengkulu
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>



    
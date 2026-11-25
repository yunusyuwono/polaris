<?php
include 'nav.php';
$hal = isset($_GET['hal']) ? $_GET['hal'] : '';
$unitClass = $hal === 'unit' ? 'active' : 'inactive';
$userClass = in_array($hal, ['user', 'edituser']) ? 'active' : 'inactive';
$standarClass = $hal === 'standar' ? 'active' : 'inactive';
?>
<style>
    .master-header {
        background: linear-gradient(135deg, #0f172a 0%, #3b82f6 100%);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.3);
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .master-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        opacity: 0.5;
        pointer-events: none;
    }
    .master-header h5 {
        color: #ffffff;
        font-weight: 800;
        font-size: 1.8rem;
        letter-spacing: 0.5px;
    }
    .master-header p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 1rem;
    }
    .master-nav-group {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .master-nav-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        border: 1px solid transparent;
    }
    .master-nav-btn.active {
        background: #ffffff;
        color: #1e3a8a;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .master-nav-btn.active:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        color: #1e3a8a;
    }
    .master-nav-btn.inactive {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .master-nav-btn.inactive:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        color: #ffffff;
    }
    .empty-state-card {
        border-radius: 1.25rem;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        padding: 5rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    .empty-state-card:hover {
        border-color: #3b82f6;
        background: #ffffff;
        box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.1);
        transform: translateY(-5px);
    }
    .empty-state-icon {
        font-size: 4rem;
        color: #94a3b8;
        margin-bottom: 1.5rem;
        transition: color 0.3s ease;
    }
    .empty-state-card:hover .empty-state-icon {
        color: #3b82f6;
    }
</style>

<div id="main">
    <header class="">
        <a href="#" class="burger-btn d-block p-2">
            <i class="fas fa-bars"></i> Menu
        </a>
    </header>
    <div class="page-content">
        <section class="row">
            <div class="col-12">
                <div class="master-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                    <div class="z-1">
                        <h5 class="mb-2">Data Master Administrasi</h5>
                        <p class="mb-0">Kelola dan atur konfigurasi unit, pengguna, serta standar dalam satu panel terpusat.</p>
                    </div>
                    <div class="master-nav-group z-1">
                        <a class="master-nav-btn <?= $unitClass; ?>" href="?hal=unit">
                            <i class="fas fa-building"></i> Kelola Unit
                        </a>
                        <a class="master-nav-btn <?= $userClass; ?>" href="?hal=user">
                            <i class="fas fa-user-friends"></i> Kelola User
                        </a>
                        <a class="master-nav-btn <?= $standarClass; ?>" href="?hal=standar">
                            <i class="fas fa-list-check"></i> Kelola Standar
                        </a>
                    </div>
                </div>
            </div>
            
            <div id="konten" class="col-12">
                <div class="animate__animated animate__fadeIn">
                <?php
                switch ($hal) {
                    case 'unit':
                        include 'unit.php';
                        break;
                    case 'user':
                        include 'user.php';
                        break;
                    case 'edituser':
                        include 'user.edit.php';
                        break;
                    case 'hapususer':
                        include 'user.hapus.php';
                        break;
                    case 'standar':
                        include 'standar.php';
                        break;
                    default:
                        echo '<div class="empty-state-card">
                                <i class="fas fa-compass empty-state-icon"></i>
                                <h4 class="mb-3 text-dark fw-bold">Pilih Menu Manajemen</h4>
                                <p class="text-muted mb-0 fs-5">Silakan pilih menu Unit, User, atau Standar di atas untuk mulai mengelola data master aplikasi.</p>
                              </div>';
                        break;
                }
                ?>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'foot.php';?>
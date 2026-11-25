# POLARIS — Sistem Informasi Audit Mutu Internal (AMI) Berbasis Risiko

**POLARIS** adalah platform enterprise untuk pelaksanaan siklus **Audit Mutu Internal (AMI)**, kepatuhan standar SPMI/ISO, dan evaluasi pengendalian berbasis risiko secara digital (*Risk-Based Internal Audit*).

Dirancang untuk mendukung tata kelola instansi (*Good Governance*), reformasi birokrasi, penjaminan mutu, serta monitoring evaluasi kinerja antar-unit kerja secara transparan dan akuntabel.

![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-777BB4?style=flat&logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?style=flat&logo=mysql&logoColor=white)
![Frontend](https://img.shields.io/badge/UI-Bootstrap%205%20%7C%20DataTables%20%7C%20Chart.js-563D7C?style=flat)
![Architecture](https://img.shields.io/badge/Architecture-Role--Based%20Workflow%20(RBAC)-00A1AD?style=flat)

---

## 🏛️ Arsitektur & Peran Pengguna (Multi-Role RBAC)

POLARIS memisahkan alur kerja audit ke dalam 3 ruang kerja independen:
1. **Administrator / Pengendali Mutu Pusat (`/`)**:
   - Master data unit kerja, auditor, standar SPMI, instrumen LED (Lembar Evaluasi Diri), dan pembagian paket audit.
   - Penjadwalan periode audit, penunjukan ketua & anggota tim auditor.
   - Monitoring progres audit seluruh unit secara real-time via Dashboard Eksekutif.
2. **Tim Auditor Internal (`/auditor/`)**:
   - **Tahap 1–4 (Audit Standar):** Telaah Checklist Catatan Audit, Pencatatan Kondisi Positif, Pengisian Temuan Audit (KTS Mayor/Minor/Observasi), Upload Dokumentasi & Bukti.
   - **Tahap 5–8 (Audit Berbasis Risiko):**
     - Catatan Persiapan Audit Lapangan & Rekap Risiko (CAD-AMI).
     - Form Isian Lembar Kerja Kinerja & Risiko (LK2 Digital).
     - **Triangulasi Bukti AMI:** Pengujian silang multi-sumber (Telaah Dokumen, Wawancara Multi-Pihak, Observasi Langsung) untuk validasi temuan.
     - Isian Permintaan Tindakan Koreksi (PTK A).
   - Generate Berita Acara Pelaksanaan Audit dan Surat Pernyataan Selesai Audit (SPSA).
3. **Unit Kerja / Auditi (`/unit/`)**:
   - Pengisian Lembar Evaluasi Diri (LED), upload bukti dukung dokumen per butir standar.
   - Klarifikasi temuan audit, input Rencana Perbaikan (Koreksi) dan Rencana Pencegahan (Tindakan Korektif).

---

## 📊 Fitur Unggulan Sistem

- **Dual-Mode Reporting (V1 & V2):**
  - **V1 (Laporan Standar):** Format konvensional ringkasan checklist temuan, kondisi positif, dan dokumentasi.
  - **V2 (Laporan Komprehensif Berbasis Risiko):** Matriks risiko, LK2 digital, sintesis triangulasi 3 sudut pandang bukti, dan rekomendasi mitigasi risiko residual.
- **Dynamic Risk Score Engine:** Kalkulasi otomatis skor risiko (*Dampak x Probabilitas*) dengan visualisasi matriks level warna (Merah, Kuning, Hijau).
- **Export & Rekapitulasi Digital:** Ekspor laporan audit komprehensif, lembar telaah, dan sertifikat/berita acara siap cetak.

---

## 🛠️ Tech Stack

- **Backend:** PHP (Procedural & Modular Architecture, MySQLi prepared interface).
- **Database:** MySQL / MariaDB (Relational structure dengan multi-level indexing).
- **UI/UX:** Bootstrap 5, Font Awesome, DataTables (Server-side & Client-side ready), Select2, SweetAlert2.
- **Reporting & Charts:** Chart.js, PHPExcel spreadsheet engine, HTML print-to-PDF engine.

---

## 🚀 Panduan Menjalankan Sistem Secara Lokal

### Prasyarat
- Server: Apache / Nginx (Laragon / XAMPP)
- PHP: 7.4 atau PHP 8.x
- Database: MySQL 5.7+ / MariaDB 10.3+

### Instalasi
1. Clone repositori ke folder kerja web server:
   ```bash
   git clone https://github.com/yunusyuwono/polaris.git
   ```
2. Siapkan database:
   - Buat database baru bernama `polaris` di phpMyAdmin / MySQL CLI.
   - Import skema struktur tabel basis data.
3. Konfigurasi koneksi:
   - Sesuaikan konfigurasi host, user, dan password pada file:
     - `koneksi.php`
     - `auditor/koneksi.php`
     - `unit/koneksi.php`
4. Akses melalui browser:
   - Halaman Utama (Admin): `http://localhost/polaris/`
   - Portal Auditor: `http://localhost/polaris/auditor/`
   - Portal Auditi Unit: `http://localhost/polaris/unit/`

---

## 📄 Lisensi
Hak Cipta © 2026 Yunus Yuwono. Dikembangkan untuk penguatan penjaminan mutu dan tata kelola instansi berbasis risiko.

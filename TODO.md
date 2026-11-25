# TODO - ISO CRUD (iso.php & iso.ajax.php)

## Langkah yang direncanakan
- [ ] 1) Update `iso.ajax.php`: ganti seluruh action yang masih mengarah ke tabel `sarmut*` menjadi query `iso*` sesuai `iso.sql`.
- [ ] 2) Update `iso.ajax.php`: sediakan endpoint yang diperlukan UI (fetch/add/update/delete/get) untuk ISO, Klausul, Subklausul, Indikator.
- [ ] 3) Update `iso.ajax.php`: tambahkan endpoint dropdown options untuk cascading.
- [ ] 4) Update `iso.php`: sesuaikan field input & hidden id agar mapping ke skema `iso.sql` (nama_iso/idiso, nomor_klausul/nama_klausul, dst.).
- [ ] 5) Update `iso.php`: sesuaikan parameter AJAX (nama field yang dikirim) agar sesuai dengan yang dipakai di `iso.ajax.php`.
- [ ] 6) Verifikasi UI: tes create/edit/delete dan DataTables serverSide di tiap tab.
- [x] Tahap persiapan: analisa struktur `iso.sql`, `iso.php`, `iso.ajax.php`.


# TaskFlow — Prosedur Deployment dan Rollback

Dokumen ini adalah prosedur umum untuk deployment TaskFlow pada VPS produksi. Perintah dijalankan oleh operator yang memiliki akses ke server; dokumen ini tidak mengubah konfigurasi server.

## Lingkungan produksi

- Direktori aplikasi: /var/www/task
- Branch: main
- URL aplikasi: http://103.119.237.50:30063/task
- Database dan kredensial dibaca dari .env produksi; jangan menyalin nilai dari .env.example ke produksi tanpa penyesuaian.

## Prasyarat sebelum deployment

1. Pastikan commit yang akan dirilis sudah berada di main dan GitHub Actions untuk commit tersebut berstatus success.
2. Catat SHA commit saat ini sebagai versi yang sedang berjalan dan SHA target sebagai versi yang akan dirilis.
3. Pastikan tidak ada perubahan lokal yang belum disimpan pada VPS.
4. Pastikan kapasitas disk mencukupi untuk backup database.
5. Buat backup database sebelum deployment, khususnya sebelum menjalankan migration.

Contoh backup (gunakan nama database dari .env; jangan menaruh file backup di dalam repository):

    cd /var/www/task
    mkdir -p /var/backups/taskflow
    mysqldump -u NAMA_USER_DB -p --single-transaction NAMA_DATABASE > /var/backups/taskflow/taskflow-YYYYMMDD-HHMMSS.sql

Perintah akan meminta password secara interaktif. Simpan backup di lokasi yang aksesnya dibatasi dan pastikan file backup tidak kosong.

## Langkah deployment

    cd /var/www/task
    git status --short
    git branch --show-current
    git pull --ff-only origin main
    composer install --no-dev --optimize-autoloader --no-interaction
    php artisan migrate:status
    php artisan migrate --force
    php artisan optimize:clear
    php artisan migrate:status

Catatan:
- Branch harus tetap main; hentikan proses jika branch atau working tree tidak sesuai.
- Periksa daftar migration Pending sebelum menjalankan migration. Jalankan migration hanya setelah backup berhasil.
- migrate:status digunakan untuk memastikan migration yang diharapkan berstatus Ran.
- Jangan jalankan migrate:fresh, migrate:refresh, atau reset/truncate database produksi.
- Tidak perlu memasang dependency testing di VPS. Pengujian dilakukan di GitHub Actions.
- Jalankan build asset hanya jika perubahan rilis memang mengubah asset Vite yang dipakai aplikasi; asset publik statis saat ini disajikan dari public/css dan public/js.

## Verifikasi setelah deployment

1. Buka URL aplikasi dan pastikan halaman login/dashboard dapat dimuat.
2. Login menggunakan akun yang berwenang.
3. Periksa Space yang dapat diakses, buka List, tampilkan Task, dan uji satu operasi non-destruktif yang relevan.
4. Pastikan halaman Activity/Settings dapat dibuka sesuai akses.
5. Periksa php artisan migrate:status dan pastikan tidak ada migration rilis yang tertinggal.
6. Jika ada error, periksa log aplikasi di storage/logs/laravel.log tanpa menyalin rahasia atau token ke laporan.

## Rollback

### Jika masalah hanya pada source code dan tidak ada migration yang mengubah schema/data

1. Hentikan perubahan lanjutan dan catat SHA target serta SHA versi sebelumnya.
2. Pastikan working tree VPS bersih.
3. Kembalikan checkout VPS ke SHA versi sebelumnya sesuai prosedur operator, lalu bersihkan cache:

       cd /var/www/task
       git status --short
       git log -5 --oneline
       git reset --hard SHA_COMMIT_SEBELUMNYA
       php artisan optimize:clear

4. Verifikasi aplikasi kembali normal.
5. Perbaikan permanen dilakukan melalui commit baru pada branch main; jangan force-push branch bersama hanya untuk rollback produksi.

### Jika migration sudah mengubah schema/data

- Jangan otomatis menjalankan php artisan migrate:rollback; periksa migration dan efeknya terlebih dahulu.
- Migration yang bersifat destruktif atau tidak kompatibel dengan versi sebelumnya mungkin memerlukan pemulihan backup database.
- Pemulihan backup mengembalikan database ke waktu backup dan dapat menghilangkan transaksi/data yang masuk setelah backup. Lakukan hanya setelah dampak kehilangan data disetujui operator/pemilik sistem.
- Jika rollback database tidak aman, prioritaskan perbaikan forward migration/source daripada memaksa rollback.

## Batas tanggung jawab

Dokumen ini tidak mengizinkan perubahan konfigurasi VPS, web server, PHP-FPM, firewall, atau database di luar langkah deployment aplikasi yang telah disetujui. Jangan mengubah .env produksi atau kredensial sebagai bagian dari commit repository.

# TaskFlow — Product Roadmap & Development Backlog

> **Arah produk:** Organize. Collaborate. Deliver.  
> **Prinsip:** Pahami sebelum mengubah. Sederhanakan tanpa menghilangkan esensi. Bangun dengan tujuan, bukan sekadar memenuhi keinginan.

- **Versi dokumen:** 1.0
- **Tanggal:** 8 November 2026
- **Repository:** `debaluk/taskflow`
- **Branch acuan:** `main`
- **Status dokumen:** Roadmap awal berdasarkan audit source code dan dokumentasi repository.

---

## 1. Tujuan roadmap

Roadmap ini menjadi panduan pengembangan TaskFlow berdasarkan kondisi implementasi yang ditemukan, bukan sekadar daftar fitur yang ingin ditambahkan.

Fokus pengembangan:

1. Memastikan fondasi aplikasi aman, konsisten, dan dapat diandalkan.
2. Menyelesaikan fitur yang sudah memiliki struktur tetapi belum berfungsi secara utuh.
3. Menguatkan pengalaman pengelolaan pekerjaan dan kolaborasi.
4. Menjaga TaskFlow tetap sederhana, ringan, dan fokus pada alur kerja.
5. Menghindari penambahan fitur yang tidak memberi manfaat nyata bagi pengguna.

Roadmap bersifat bertahap dan dapat disesuaikan berdasarkan hasil pengujian, kebutuhan pengguna, serta kapasitas pengembangan. Estimasi bulan pada fase merupakan panduan perencanaan, bukan janji tanggal rilis.

## 2. Prinsip pengembangan

- **Audit sebelum perubahan.** Pahami implementasi dan dampak perubahan sebelum menyentuh kode.
- **Selesaikan yang ada sebelum menambah yang baru.** Fitur setengah jadi lebih penting diselesaikan daripada menumpuk fitur baru.
- **Backend sebagai sumber kebenaran.** Validasi, otorisasi, dan aturan bisnis wajib ditegakkan di server.
- **Tidak merusak perilaku yang sudah benar.** Perubahan harus terbatas pada kebutuhan yang disepakati.
- **Uji sebelum dinyatakan selesai.** Setiap pekerjaan memiliki kriteria penerimaan dan pengujian yang jelas.
- **Sederhana secara sengaja.** Tidak mengejar kelengkapan fitur aplikasi lain.
- **Dokumentasi mengikuti implementasi.** README dan dokumen teknis harus mencerminkan kondisi aktual.

## 3. Inventaris fitur saat audit

Status di bawah membedakan keberadaan struktur kode dari bukti bahwa fitur telah berfungsi end-to-end. Audit dilakukan terhadap source code dan dokumentasi pada repository, bukan pengujian langsung seluruh alur di server produksi.

### 3.1 Fitur yang sudah memiliki implementasi

| Area | Temuan audit | Status awal |
|---|---|---|
| Autentikasi | Login, registrasi, logout | Implementasi tersedia |
| Workspace | CRUD dan mekanisme switch/clear workspace | Implementasi tersedia; pemilihan workspace perlu audit |
| Space | CRUD dan toggle | Implementasi tersedia |
| Folder | CRUD dan toggle | Implementasi tersedia |
| List | CRUD, toggle, halaman list | Implementasi tersedia |
| Task | CRUD, detail, status, prioritas, due date | Implementasi tersedia; perlu uji alur menyeluruh |
| Assignee | Pengelolaan member dan penugasan task | Implementasi tersedia; perlu uji otorisasi |
| Subtask | CRUD menggunakan relasi parent task | Implementasi tersedia |
| Dependency | Penyimpanan dan penghapusan dependency | Implementasi tersedia; perlu uji aturan relasi |
| Board | Tampilan Kanban dan endpoint reorder | Implementasi tersedia; perlu uji drag-and-drop |
| Calendar | Tampilan kalender task | Implementasi tersedia |
| Gantt | Tampilan Gantt dan navigasi periode | Implementasi tersedia; perlu uji sinkronisasi data |
| Member | Daftar, tambah, hapus, pencarian pengguna | Implementasi tersedia |
| Activity | Model, controller, dan halaman pengelolaan tersedia | Pencatatan otomatis belum terverifikasi |
| ClickUp | Endpoint OAuth connect/callback | OAuth tersedia; proses import belum terbukti tuntas |

### 3.2 Fitur yang belum lengkap atau perlu verifikasi

| Area | Temuan audit | Tindak lanjut |
|---|---|---|
| Dashboard | Ringkasan task tersedia; panel recent activity masih kosong | Hubungkan dengan data aktivitas aktual |
| Agenda | UI tersedia, tetapi controller mengirim koleksi kosong dan hitungan nol | Implementasikan query agenda |
| Inbox | Controller mengirim koleksi kosong; view pada path tasks/inbox berisi konten Settings | Audit pemisahan view dan bangun Inbox yang benar |
| Favorites | Controller mengirim koleksi kosong; README menyebut fitur masih ditunda/disembunyikan | Tetapkan keputusan produk sebelum implementasi |
| Activity log | Infrastruktur model/controller tersedia, tetapi pencatatan otomatis belum terbukti | Tentukan event dan implementasikan pencatatan |
| Settings | Pengaturan umum dan notifikasi masih berupa placeholder | Tentukan kebutuhan minimum dan simpan konfigurasi |
| ClickUp import | OAuth tersedia, alur import end-to-end belum terverifikasi | Audit command, mapping, validasi, dan logging |
| Pengujian otomatis | Cakupan pengujian bisnis belum memadai/terbukti dari audit awal | Bangun feature test untuk alur kritis |

### 3.3 Temuan teknis yang perlu ditangani

1. **Workspace aktif masih hardcoded.** Method `currentWorkspace()` memilih workspace dengan ID 1 dan status aktif. Ini tidak sejalan dengan kebutuhan workspace yang dinamis dan harus diaudit bersama mekanisme kepemilikan serta otorisasi.
2. **Route detail task terdaftar dua kali.** Duplikasi route `tasks.show` perlu dibersihkan setelah memastikan tidak ada ketergantungan.
3. **Inbox dan Settings tidak konsisten.** Route/controller Inbox mengarah ke view yang kontennya justru menyerupai Settings. Perlu memastikan view Settings yang benar sebelum memindahkan atau mengubah file.
4. **Token ClickUp tersimpan di session.** Perlu dipastikan desain penyimpanan koneksi yang aman dan berkelanjutan per pengguna sebelum integrasi dinyatakan siap.
5. **Dokumentasi dan implementasi harus diselaraskan.** Khususnya status Favorites, Activity, Inbox, Agenda, dan ClickUp import.

> Catatan: temuan di atas adalah hasil pemeriksaan source code. Jangan menganggapnya sebagai bukti bahwa seluruh skenario gagal di lingkungan runtime. Lakukan reproduksi dan pengujian sebelum menetapkan perubahan.

## 4. Pilar produk

### Organize
Membantu pengguna menyusun pekerjaan dengan struktur yang mudah dipahami: Workspace, Space, Folder (opsional), List, Task, dan Subtask.

### Collaborate
Membantu anggota tim memahami penugasan, perubahan, komentar, dan hal-hal yang membutuhkan perhatian.

### Deliver
Membantu pengguna melihat tenggat, prioritas, progres, dan pekerjaan yang perlu diselesaikan.

Setiap fitur baru harus mendukung minimal satu pilar tersebut dan memiliki alasan penggunaan yang jelas.

## 5. Tahapan roadmap

### Fase 1 — Stabilization & Foundation

**Prioritas: P0 — wajib didahulukan**

Tujuan: memastikan fondasi aplikasi aman dan perilaku inti dapat diandalkan.

Backlog:
- [ ] Audit mekanisme workspace aktif, kepemilikan, dan akses pengguna.
- [ ] Hilangkan ketergantungan hardcoded workspace ID setelah desain pemilihan workspace disepakati.
- [ ] Audit authorization pada workspace, space, folder, list, task, subtask, dan dependency.
- [ ] Verifikasi alur CRUD task dan subtask.
- [ ] Verifikasi penugasan satu atau beberapa assignee.
- [ ] Verifikasi perubahan status, prioritas, due date, dan reorder.
- [ ] Verifikasi dependency dan dampaknya pada Gantt.
- [ ] Bersihkan duplikasi route setelah memastikan route yang digunakan.
- [ ] Rapikan ketidaksesuaian route, controller, view, dan dokumentasi.
- [ ] Tambahkan feature test untuk alur bisnis dan batas otorisasi.
- [ ] Audit validasi input, penanganan error, dan keamanan data.

Kriteria selesai:
- Pengguna hanya dapat mengakses data sesuai haknya.
- Workspace aktif ditentukan melalui mekanisme yang jelas, bukan ID statis.
- Alur task inti lulus pengujian.
- Tidak ada route duplikat atau mismatch yang diketahui pada area yang disentuh.
- Pengujian regresi untuk alur kritis tersedia dan lulus.

### Fase 2 — Task Management Experience

**Prioritas: P1**

Tujuan: membuat pengelolaan pekerjaan lebih lengkap tanpa membuat antarmuka menjadi rumit.

Backlog:
- [ ] Audit dan sempurnakan halaman detail task.
- [ ] Tentukan format deskripsi task yang aman dan nyaman digunakan.
- [ ] Tambahkan komentar pada task.
- [ ] Tambahkan lampiran task jika kebutuhan dan batas penyimpanannya sudah jelas.
- [ ] Sediakan pencarian task yang relevan.
- [ ] Sempurnakan filter, sorting, dan navigasi task.
- [ ] Evaluasi kebutuhan custom status/workflow per Space.
- [ ] Samakan perilaku dan data antara List, Board, Calendar, dan Gantt.
- [ ] Pastikan perubahan pada satu tampilan tercermin konsisten di tampilan lain.

Kriteria selesai:
- Pengguna dapat memahami konteks dan memperbarui task dari halaman detail.
- Komentar terhubung ke task dan memiliki otorisasi yang benar.
- Filter dan pencarian menghasilkan data sesuai workspace dan akses pengguna.
- Tampilan berbeda menyajikan data task yang konsisten.

### Fase 3 — Collaboration & Notification

**Prioritas: P2**

Tujuan: membantu tim mengetahui perubahan dan pekerjaan yang membutuhkan perhatian.

Backlog:
- [ ] Definisikan skema event Activity yang konsisten.
- [ ] Catat event penting: task dibuat, diperbarui, status berubah, assignee berubah, due date berubah, komentar, dan perubahan member.
- [ ] Bangun Inbox berdasarkan event yang relevan.
- [ ] Tambahkan status dibaca/belum dibaca.
- [ ] Implementasikan notifikasi penugasan dan perubahan task.
- [ ] Evaluasi mention pengguna pada komentar.
- [ ] Implementasikan Agenda dari due date dan status task aktual.
- [ ] Tentukan aturan notifikasi agar tidak berlebihan.

Kriteria selesai:
- Aktivitas penting tercatat dengan aktor, waktu, objek, dan konteks yang benar.
- Inbox menampilkan item yang relevan untuk pengguna yang sedang login.
- Status baca/belum dibaca tersimpan dengan benar.
- Agenda menampilkan pekerjaan hari ini, mendatang, dan terlambat dari data aktual.
- Notifikasi tidak bocor antar pengguna atau workspace.

### Fase 4 — Productivity & Overview

**Prioritas: P3**

Tujuan: menyediakan gambaran progres dan beban kerja yang benar-benar membantu pengambilan keputusan.

Backlog:
- [ ] Hubungkan recent activity dashboard dengan Activity log.
- [ ] Pastikan ringkasan Today, My Tasks, Attention, dan Spaces menggunakan query aktual.
- [ ] Tambahkan ringkasan progres per Space/List.
- [ ] Tambahkan workload per member jika data dan aturan pengukurannya sudah jelas.
- [ ] Sediakan pemantauan overdue.
- [ ] Evaluasi tren penyelesaian task.
- [ ] Putuskan apakah Favorites layak diaktifkan dan bagaimana perilakunya.
- [ ] Tambahkan laporan produktivitas sederhana bila dibutuhkan pengguna.

Kriteria selesai:
- Angka dashboard dapat ditelusuri ke data task yang mendasarinya.
- Definisi metrik konsisten dan terdokumentasi.
- Workload tidak menyesatkan karena perhitungan assignee atau status yang tidak konsisten.
- Fitur Favorites hanya dirilis jika perilaku, query, dan pengujiannya sudah lengkap.

### Fase 5 — Integration & Data Portability

**Prioritas: P4**

Tujuan: memudahkan migrasi dan pertukaran data tanpa mengorbankan integritas struktur TaskFlow.

Backlog:
- [ ] Audit implementasi OAuth ClickUp dan pengelolaan token.
- [ ] Tetapkan penyimpanan koneksi integrasi yang aman dan sesuai pengguna.
- [ ] Lengkapi import Workspace/Space/Folder/List/Task.
- [ ] Lengkapi mapping Member dan Assignee.
- [ ] Mapping status, prioritas, due date, subtask, dan dependency jika didukung sumber.
- [ ] Sediakan preview dan validasi sebelum import.
- [ ] Catat hasil import, kegagalan, dan item yang dilewati.
- [ ] Pastikan import dapat dijalankan ulang tanpa duplikasi yang tidak diinginkan.
- [ ] Evaluasi export data TaskFlow.

Kriteria selesai:
- Pengguna memahami data yang akan diimpor sebelum proses dijalankan.
- Mapping dan hasil import dapat ditelusuri.
- Kegagalan tidak meninggalkan data parsial tanpa informasi.
- Kredensial integrasi tidak terekspos dan tidak bergantung pada session sementara.
- Pengujian import mencakup duplikasi, data tidak valid, dan kegagalan parsial.

### Fase 6 — Product Readiness

**Prioritas: P5**

Tujuan: menyiapkan TaskFlow untuk penggunaan tim yang lebih luas.

Backlog:
- [ ] Evaluasi role dan permission minimum.
- [ ] Lengkapi pengaturan profil.
- [ ] Lengkapi pengaturan notifikasi.
- [ ] Sediakan audit trail untuk aktivitas penting.
- [ ] Audit backup dan prosedur pemulihan.
- [ ] Optimalkan query dan performa pada data yang bertambah.
- [ ] Audit keamanan autentikasi, session, upload, dan integrasi.
- [ ] Lengkapi dokumentasi pengguna dan administrator.
- [ ] Lakukan uji coba dengan pengguna/tim dan kumpulkan umpan balik.

Kriteria selesai:
- Hak akses dapat dipahami dan diuji.
- Prosedur backup dan pemulihan terdokumentasi.
- Performa dan keamanan area kritis telah dievaluasi.
- Dokumentasi sesuai dengan fitur yang benar-benar dirilis.
- Uji coba pengguna menghasilkan daftar perbaikan yang dapat ditindaklanjuti.

## 6. Urutan prioritas

| Prioritas | Fokus | Alasan |
|---|---|---|
| P0 | Stabilitas, workspace, authorization, pengujian | Fondasi semua fitur |
| P1 | Pengalaman task dan detail task | Aktivitas utama produk |
| P2 | Activity, Inbox, Agenda, notifikasi | Kolaborasi dan perhatian |
| P3 | Dashboard, workload, produktivitas | Gambaran progres |
| P4 | ClickUp import dan export | Migrasi dan portabilitas |
| P5 | Role, keamanan, performa, kesiapan tim | Kesiapan penggunaan lebih luas |

Urutan ini lebih penting daripada target bulan. Fase berikutnya tidak harus dimulai hanya karena waktu berjalan; pastikan kriteria fase sebelumnya terpenuhi.

## 7. Fitur yang belum perlu diprioritaskan

Untuk menjaga fokus dan menghindari kompleksitas terlalu dini, fitur berikut tidak menjadi prioritas roadmap awal:

- AI task assistant.
- Chat internal seperti aplikasi pesan instan.
- Workflow automation yang kompleks.
- Banyak integrasi pihak ketiga sekaligus.
- Whiteboard dan kolaborasi dokumen yang luas.
- Fitur enterprise yang belum memiliki kebutuhan nyata.

Fitur tersebut dapat dievaluasi kembali jika penggunaan TaskFlow menunjukkan kebutuhan yang jelas.

## 8. Definition of Done

Sebuah pekerjaan hanya dapat dinyatakan selesai jika:

1. Tujuan dan ruang lingkup perubahan disepakati.
2. Dampak terhadap struktur data, otorisasi, dan fitur lain telah diaudit.
3. Implementasi mengikuti pola dan konvensi proyek.
4. Validasi backend dan penanganan error tersedia.
5. Pengujian yang relevan dijalankan dan hasilnya dicatat.
6. Tidak ada regresi pada alur yang sudah benar.
7. Dokumentasi diperbarui jika perilaku atau arsitektur berubah.
8. Perubahan di-commit dengan pesan yang menjelaskan maksudnya.
9. Pengujian di lingkungan target dilakukan sebelum menyatakan rilis berhasil.

## 9. Cara mengelola backlog

Gunakan status berikut untuk setiap item:

- **Planned** — sudah masuk backlog, belum dikerjakan.
- **In Progress** — sedang diimplementasikan.
- **Testing** — implementasi selesai dan sedang diverifikasi.
- **Done** — kriteria penerimaan terpenuhi dan hasil pengujian tercatat.
- **Blocked** — tertahan oleh keputusan, dependensi, atau masalah teknis.

Setiap item backlog sebaiknya memiliki:
- Masalah atau kebutuhan yang ingin diselesaikan.
- Ruang lingkup dan batasan perubahan.
- Dampak pada data dan otorisasi.
- Kriteria penerimaan.
- Skenario pengujian.
- Status dan catatan keputusan.

Roadmap ini adalah dokumen induk. Detail pekerjaan teknis dapat dipecah menjadi issue GitHub agar diskusi, implementasi, dan hasil pengujian dapat ditelusuri.

## 10. Langkah pengembangan berikutnya

Mulai dari **Fase 1 — Stabilization & Foundation**.

Langkah pertama bukan langsung mengubah banyak kode, melainkan:
1. Audit lengkap alur workspace dan authorization.
2. Petakan skenario uji task, subtask, assignee, dan dependency.
3. Konfirmasi temuan yang benar-benar terjadi.
4. Susun perubahan minimum berdasarkan hasil audit.
5. Implementasikan dan uji satu lingkup perubahan pada satu waktu.

**Arah akhir:** TaskFlow tidak harus memiliki fitur sebanyak aplikasi lain. TaskFlow harus membuat pekerjaan lebih teratur, kolaborasi lebih jelas, dan penyelesaian pekerjaan lebih mudah.

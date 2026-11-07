# TaskFlow Product Roadmap & Development Backlog

> Dokumen kerja pengembangan TaskFlow. Roadmap ini disusun berdasarkan audit source code pada branch `main`, bukan hasil pengujian menyeluruh pada server produksi.
>
> **Terakhir diperbarui:** 8 November 2026  
> **Branch pengembangan:** `main`  
> **Prinsip:** Pahami sebelum mengubah. Sederhanakan tanpa menghilangkan esensi. Bangun dengan tujuan, bukan sekadar memenuhi keinginan.

## 1. Arah Produk

TaskFlow dikembangkan sebagai aplikasi manajemen pekerjaan yang sederhana, terarah, dan mudah digunakan. Tujuannya bukan menyalin seluruh kompleksitas ClickUp, melainkan membantu tim mengorganisasi pekerjaan, berkolaborasi, dan menyelesaikannya dengan lebih baik.

Tiga pilar produk:

1. **Organize** — menyusun ruang kerja, daftar, tugas, prioritas, dan jadwal.
2. **Collaborate** — berbagi tanggung jawab, memberi konteks, dan mengikuti perubahan pekerjaan.
3. **Deliver** — memantau progres dan memastikan pekerjaan selesai.

Prinsip pengembangan:
- Utamakan stabilitas dan kejelasan alur sebelum menambah fitur.
- Pertahankan perilaku yang sudah benar.
- Audit sebelum perubahan; perubahan harus sesuai kebutuhan dan ruang lingkup.
- Otorisasi harus ditegakkan di backend, bukan hanya disembunyikan di UI.
- Setiap fitur harus memiliki kriteria penerimaan dan pengujian.
- Hindari kompleksitas yang belum dibutuhkan pengguna.

## 2. Inventaris Fitur Saat Audit

Status berikut menggambarkan keberadaan implementasi pada source code. Status **Ada di source** tidak otomatis berarti fitur telah lulus pengujian end-to-end.

| Area | Kondisi yang ditemukan | Status audit |
|---|---|---|
| Autentikasi | Login, registrasi, logout | Ada di source |
| Struktur kerja | Workspace, Space, Folder, List | CRUD tersedia; perlu validasi skenario akses |
| Task | CRUD, status, prioritas, tanggal jatuh tempo | Ada di source |
| Penugasan | Member dan multi-assignee | Struktur/controller tersedia |
| Subtask | Menggunakan tabel tasks dengan parent_id | CRUD tersedia |
| Dependency | Penyimpanan dependency dan tampilan Gantt | Ada di source; perlu uji konsistensi |
| Tampilan kerja | List, Board, Calendar, Gantt | Route/controller/view tersedia |
| Dashboard | Ringkasan tugas, tugas saya, overdue, Space | Komponen tersedia; recent activity masih kosong |
| Agenda | UI agenda tersedia | Controller masih mengirim data kosong |
| Inbox | Route/controller tersedia | Belum berfungsi; view yang dirujuk berisi konten Settings |
| Favorites | Route/controller/view tersedia | Controller mengirim koleksi kosong; dokumentasi menyebut fitur belum diaktifkan |
| Activity | Model dan halaman pengelolaan tersedia | Pencatatan otomatis belum terverifikasi |
| Settings | Halaman pengaturan dasar dan ClickUp | Sebagian konten masih placeholder |
| ClickUp | OAuth connect/callback | OAuth tersedia; alur import end-to-end belum terverifikasi |

### Catatan arsitektur dari audit

- Workspace ID `1` memang ditetapkan sebagai wadah data internal TaskFlow, bukan konteks kerja atau batas otorisasi pengguna. Akses pengguna harus ditentukan pada tingkat Space.
- Route detail Task ditemukan didefinisikan dua kali.
- Controller Agenda dan Inbox masih mengirim data kosong.
- View Inbox yang dirujuk controller tidak sesuai dengan fungsi Inbox.
- Dashboard belum menampilkan aktivitas terbaru yang nyata.
- OAuth ClickUp menyimpan token pada session dalam implementasi yang diperiksa; penyimpanan koneksi jangka panjang per pengguna belum terlihat.
- Keberadaan Activity model/halaman belum membuktikan semua perubahan Task otomatis tercatat.
- Pengujian otomatis yang memadai untuk alur bisnis utama belum terkonfirmasi dalam audit ini.

## 3. Prioritas Roadmap

| Fase | Fokus | Prioritas |
|---|---|---|
| 1. Stabilization & Foundation | Keamanan akses, konsistensi arsitektur, pengujian fondasi | P0 — dikerjakan lebih dahulu |
| 2. Task Management Experience | Detail Task, komentar, pencarian, filter, dan penyempurnaan tampilan kerja | P1 |
| 3. Collaboration & Notification | Activity, Inbox, notifikasi, mention, dan Agenda | P2 |
| 4. Productivity & Overview | Dashboard, workload, metrik progres, dan Favorites | P3 |
| 5. Integration & Data Portability | Import ClickUp yang lengkap, validasi, log, dan export | P4 |
| 6. Product Readiness | Role/permission, audit trail, backup, performa, dokumentasi, dan uji tim | P5 |

Estimasi enam fase adalah kerangka perencanaan, bukan tenggat yang mengikat. Durasi aktual ditentukan oleh hasil audit, kapasitas, dan hasil pengujian setiap fase.

---

# 4. Fase 1 — Stabilization & Foundation


**Tujuan:** memastikan fondasi TaskFlow aman, konsisten, dapat diuji, dan tidak menimbulkan regresi sebelum pengembangan fitur baru.

**Batasan fase:** fokus pada audit, koreksi masalah fondasi yang terbukti, dan pengujian. Tidak menambahkan fitur kolaborasi atau produktivitas baru pada fase ini.

## 4.1 Backlog Fase 1

### P0 — Wajib diselesaikan

- [ ] **F1-01 — Audit konteks Workspace aktif**
  - Telusuri seluruh alur pemilihan Workspace dan relasi user–Workspace.
  - Pastikan query Task, Space, Folder, List, dan anggota selalu dibatasi pada Workspace yang memang boleh diakses.
  - Pertahankan Workspace ID `1` sebagai wadah internal; jangan membangun mekanisme multi-Workspace atau mengganti scope ini tanpa keputusan baru.
  - Pastikan seluruh akses data kerja ditentukan melalui keanggotaan dan kepemilikan Space.
  - Kriteria penerimaan: Workspace tetap menggunakan ID `1`, sementara pengguna hanya dapat mengakses Space yang dimiliki atau dibagikan kepadanya.

  - **Status audit: Testing.** Audit source dan koreksi minimum F1-01 telah dilakukan. Pemenuhan akses per Space tetap menjadi ruang lingkup implementasi otorisasi F1-02.
  - **Temuan audit (8 November 2026):**
    - `TaskManagerController::currentWorkspace()` mengunci Workspace ID `1` dan mengatur session ke ID yang sama. Perilaku ini sesuai keputusan arsitektur dan harus dipertahankan.
    - `TaskManagerController::accessibleWorkspaceIds()` mengambil ID dari `workspace_members`, tetapi alur utama Task tidak menggunakan hasil tersebut sebagai scope.
    - `AuthController::register()` membuat User dan login, tetapi tidak membuat relasi `WorkspaceMember`.
    - Dashboard mengambil Task dan Space dari seluruh Workspace `1`; belum ada filter berdasarkan kepemilikan/keanggotaan Space.
    - Query List, Board, Calendar, Gantt, `resolveSpace()`, `resolveTaskList()`, `scopedTasksQuery()`, `listPage()`, dan `authorizeTask()` memeriksa Workspace `1`/relasi objek, tetapi belum memeriksa apakah User memiliki akses ke Space.
    - `FolderController::authorizeWorkspace()` masih memeriksa `WorkspaceMember`, sedangkan `TaskListController::authorizeSpace()` hanya memeriksa `workspace_id = 1`. Kedua pola ini tidak konsisten.
    - `SpaceController` membatasi data ke Workspace `1`; controller mengirim `owner_id` saat membuat Space, tetapi atribut tersebut belum tercantum dalam `Space::$fillable`, sehingga penyimpanan Owner perlu diperbaiki pada F1-02.
    - `WorkspaceController` masih menyediakan operasi create/update/toggle/delete dan switch Workspace. Namun alur Task utama tetap memaksa Workspace `1`; pengelolaan Workspace legacy ini perlu ditinjau agar tidak memberi kesan bahwa Workspace dapat dipilih sebagai konteks kerja.
    - `routes/web.php` menempatkan seluruh route Workspace di middleware `auth`, tetapi route create/update/toggle/delete/switch/clear/show tetap dapat dipanggil langsung. Tidak ditemukan tautan pemilihan Workspace pada sidebar utama.
    - `resources/views/layouts/task-manager.blade.php` membangun pohon Space langsung dari `workspace_id = 1`; tidak membaca session Workspace sebagai pilihan konteks.
    - `resources/views/workspaces/index.blade.php` masih menyediakan UI untuk membuat, mengubah, menonaktifkan, dan menghapus Workspace. Ini bertentangan dengan keputusan produk bahwa Workspace bukan fitur multi-tenant yang digunakan.
    - `WorkspaceMemberController` masih berisi operasi anggota Workspace, tetapi route untuk controller tersebut tidak terdaftar pada `routes/web.php`; halaman `workspaces.show` merujuk nama route `workspace.members` yang tidak ditemukan pada daftar route.
    - `FolderController::authorizeWorkspace()` memerlukan baris aktif di `workspace_members`. Sementara `AuthController::register()` tidak membuat baris tersebut. Ini membuat akses Folder bergantung pada relasi Workspace legacy, bukan konteks Workspace internal dan keanggotaan Space.
  - **Dampak:** Workspace `1` telah menjadi scope internal pada alur utama, tetapi belum menjadi mekanisme pembatas akses pengguna. Pembatasan yang benar harus memakai kepemilikan dan keanggotaan Space sesuai keputusan F1-02.
  - **Implementasi koreksi minimum (8 November 2026):**
    - Route manajemen Workspace lama (index/store/update/toggle/destroy/show), switch, dan clear dihapus dari `routes/web.php`. Workspace tidak lagi dapat dikelola atau dipilih sebagai konteks kerja melalui endpoint aplikasi.
    - Import controller Workspace dan WorkspaceMember yang tidak lagi dipakai pada file route dihapus.
    - Route Folder tetap dipertahankan; parameter Workspace pada route tersebut tetap menjadi bagian struktur internal, bukan fitur pemilihan Workspace.
    - Tabel, model, dan relasi Workspace tidak dihapus; Workspace ID `1` tetap menjadi wadah teknis.
  - **Pengujian ditambahkan:** `tests/Feature/WorkspaceContextTest.php` memastikan route manajemen/switch Workspace tidak terdaftar dan route Folder tetap tersedia. Hasil eksekusi PHPUnit belum dijalankan pada lingkungan ini.
  - **Commit kode:** `c0e41fb` (disable route) dan `3f82a8e` (feature test).
  - **Otomasi pengujian CI (8 November 2026):** workflow `.github/workflows/tests.yml` ditambahkan menggunakan GitHub Actions. Workflow berjalan pada push ke `main`, Pull Request menuju `main`, dan dapat dijalankan manual (`workflow_dispatch`). Runner menggunakan PHP 8.3, ekstensi SQLite, memasang dependency Composer termasuk `require-dev`, menyiapkan `.env`, lalu menjalankan `php artisan test`. Pengujian tidak dijalankan di VPS produksi dan tidak memerlukan perubahan dependency server.
  - **Koreksi CI (8 November 2026):** eksekusi pertama berhasil memasang dependency, tetapi berhenti pada persiapan environment karena repository tidak menyediakan `.env.example`. Workflow diperbaiki untuk membuat `.env` dengan variabel `APP_KEY` kosong sebelum menjalankan `php artisan key:generate`. Eksekusi berikutnya berhasil melewati persiapan environment setelah `APP_KEY=` ditulis eksplisit, lalu test suite menemukan kegagalan pada test bawaan `ExampleTest`: test mengharapkan HTTP 200 untuk `/`, padahal route dashboard memang dilindungi middleware `auth` dan guest diarahkan (HTTP 302). Test diperbaiki agar memverifikasi redirect guest sesuai perilaku aplikasi. Pengujian ulang dipicu oleh commit perbaikan tersebut.
  - **Commit terkait:** `6719a0a` (workflow awal), `9463362` (koreksi persiapan environment awal), `5c9aa34` (inisialisasi variabel APP_KEY), `f156698` (selaraskan test dashboard dengan middleware auth), `419790c` dan `dac5087` (pencatatan workflow/koreksi pada roadmap).
  - **Status:** Testing — workflow CI sudah diperbaiki; menunggu hasil eksekusi ulang setelah koreksi test dashboard. F1-01 belum dinyatakan Done.

- [ ] **F1-02 — Audit otorisasi objek**
  - Terapkan otorisasi berbasis Space untuk Task, Subtask, Folder, List, anggota, dan Dependency.
  - Bentuk relasi anggota pada tingkat Space. Satu Space dapat dimiliki satu Owner dan dibagikan kepada beberapa Member.
  - Owner Space dapat melihat dan mengelola seluruh Task di Space serta mengelola anggota dan pengaturan Space.
  - Member Space dapat melihat Task dan membuat Task baru di Space yang dibagikan kepadanya.
  - Pembuat Task (`created_by`) dapat mengelola detail dan menghapus Task yang dibuatnya sendiri.
  - Member yang hanya ditugaskan sebagai assignee tidak boleh mengubah detail atau menghapus Task; ia hanya boleh memperbarui status dan menambahkan note/catatan.
  - Jika pengguna adalah pembuat sekaligus assignee, hak sebagai pembuat Task yang berlaku.
  - Member tidak boleh mengelola anggota atau menghapus Space. Assignment hanya boleh diberikan kepada pengguna yang menjadi anggota Space terkait.
  - Terapkan aturan pada seluruh endpoint backend, termasuk update, delete, status, assignment, reorder/Board, Subtask, dan Dependency; UI bukan pengganti otorisasi backend.
  - Jangan mengubah tabel `members` global yang telah digunakan untuk identitas/relasi ClickUp menjadi pivot keanggotaan Space. Audit pemakaiannya dan buat relasi Space tersendiri bila diperlukan.
  - Kriteria penerimaan: request tanpa hak akses ditolak konsisten; pembuat, assignee, dan Owner memperoleh hak sesuai matriks; manipulasi URL/request tidak dapat melewati aturan.

- [ ] **F1-03 — Rapikan definisi route**
  - Verifikasi route detail Task yang terduplikasi.
  - Hapus definisi duplikat setelah dipastikan tidak ada perbedaan middleware atau perilaku.
  - Periksa konflik nama route dan urutan route dinamis.
  - Kriteria penerimaan: setiap endpoint memiliki definisi yang jelas dan route:list tidak menunjukkan konflik yang tidak disengaja.

- [ ] **F1-04 — Bangun pengujian alur inti**
  - Siapkan feature test untuk autentikasi dan akses Workspace.
  - Uji CRUD Task, perubahan status/prioritas, assignment, Subtask, dan Dependency.
  - Uji pembatasan akses lintas pengguna/Workspace.
  - Siapkan dan pelihara GitHub Actions untuk menjalankan test suite secara otomatis pada push ke `main` dan Pull Request menuju `main`; pengujian tidak bergantung pada PHPUnit di VPS produksi.
  - Kriteria penerimaan: skenario inti berjalan otomatis, workflow CI berstatus sukses, dan hasil pengujian dapat diulang.

### Ketentuan Role dan Hak Akses Space (keputusan produk)

Ketentuan berikut telah disepakati dan menjadi acuan implementasi F1-02. Ini adalah role pada tingkat Space, bukan role Workspace global.

| Aktivitas | Owner Space | Member pembuat Task | Member yang hanya ditugaskan |
|---|---|---|---|
| Melihat Space dan Task | Semua di Space miliknya | Ya, pada Space yang dibagikan | Ya, pada Space yang dibagikan |
| Membuat Task | Ya | Ya | Ya |
| Mengedit detail Task | Semua Task | Task yang dibuat sendiri | Tidak |
| Mengubah status Task | Semua Task | Task yang dibuat sendiri | Ya, hanya Task yang ditugaskan kepadanya |
| Menambahkan note/catatan | Semua Task | Task yang dibuat sendiri | Ya, hanya Task yang ditugaskan kepadanya |
| Menghapus Task | Semua Task | Task yang dibuat sendiri | Tidak |
| Mengelola anggota Space | Ya | Tidak | Tidak |
| Mengubah/menghapus Space | Ya | Tidak | Tidak |

Aturan tambahan:
- `created_by` menjadi sumber kebenaran untuk menentukan pembuat Task.
- Relasi `task_assignees` menjadi sumber kebenaran untuk menentukan pengguna yang ditugaskan.
- Jika pengguna adalah pembuat sekaligus assignee, hak pembuat Task yang berlaku.
- Keanggotaan Space menentukan apakah pengguna boleh masuk dan melihat Space; status pembuat/assignee menentukan tindakan yang boleh dilakukan pada Task.
- Note berarti menambahkan catatan; hak mengedit atau menghapus note milik pengguna lain tidak diberikan oleh keputusan ini.
- Struktur Workspace tetap satu wadah internal dengan `workspace_id = 1`. Tidak ada perubahan ke model multi-Workspace.
- Detail hak Subtask, Dependency, dan operasi reorder harus mengikuti otorisasi induknya dan tidak boleh menjadi jalur untuk melewati batas hak Task.
- Implementasi fitur note perlu diaudit terlebih dahulu. Jika belum tersedia, catat sebagai dependensi fitur; jangan menganggap endpoint note sudah ada.
### P1 — Penting setelah P0

- [ ] **F1-05 — Audit validasi dan konsistensi request**
  - Periksa validasi input pada endpoint utama.
  - Pastikan respons sukses/gagal konsisten dan kesalahan tidak membocorkan detail sensitif.
  - Kriteria penerimaan: input tidak valid ditolak dengan pesan yang layak dan data tidak berubah sebagian.

- [ ] **F1-06 — Audit relasi dan integritas data**
  - Periksa relasi parent–subtask, assignee, dependency, dan struktur Workspace.
  - Tinjau perilaku penghapusan dan foreign key agar tidak meninggalkan data yatim.
  - Kriteria penerimaan: operasi perubahan/penghapusan menjaga integritas relasi.

- [ ] **F1-07 — Selaraskan dokumentasi dengan implementasi**
  - Perbarui README bila deskripsi fitur tidak sesuai dengan kondisi aktual.
  - Tegaskan fitur yang masih placeholder atau belum aktif.
  - Kriteria penerimaan: dokumentasi tidak menyatakan fitur selesai jika implementasinya belum tersedia atau belum teruji.

- [ ] **F1-08 — Verifikasi konfigurasi dan kesiapan rilis**
  - Periksa konfigurasi environment, session, logging, cache, dan penanganan error tanpa mengubah konfigurasi server di luar kebutuhan.
  - Pastikan langkah deploy dan rollback terdokumentasi.
  - Kriteria penerimaan: prosedur rilis dapat diikuti dan verifikasi pascadeploy jelas.

## 4.2 Urutan Pelaksanaan

1. Audit Workspace dan otorisasi; dokumentasikan alur aktual sebelum mengubah kode.
2. Buat daftar temuan dengan bukti file, method, route, dan dampaknya.
3. Sepakati koreksi minimum untuk temuan yang terbukti.
4. Implementasikan koreksi satu ruang lingkup pada satu waktu.
5. Jalankan test suite dan pemeriksaan regresi.
6. Tinjau diff, commit ke `main`, lalu lakukan verifikasi setelah deploy.

## 4.3 Definition of Done Fase 1

Fase 1 dinyatakan selesai jika:

- [ ] Tidak ada akses lintas Workspace yang tidak sah pada skenario pengujian.
- [ ] Otorisasi backend teruji untuk objek dan operasi utama.
- [ ] Definisi route duplikat/konflik yang tidak disengaja telah dibereskan.
- [ ] Alur Task, Subtask, Assignment, dan Dependency memiliki pengujian otomatis.
- [ ] Integritas relasi dan perilaku penghapusan telah diverifikasi.
- [ ] Dokumentasi sesuai dengan implementasi aktual.
- [ ] Tidak ada regresi pada alur kerja inti.
- [ ] Hasil test dan catatan perubahan tersedia sebelum fase berikutnya dimulai.

---

# 5. Fase 2 — Task Management Experience

**Tujuan:** membuat pengelolaan satu pekerjaan terasa lengkap dan efisien.

- [ ] Penyempurnaan halaman/detail Task sebagai pusat konteks pekerjaan.
- [ ] Komentar dan percakapan yang melekat pada Task.
- [ ] Pencarian Task lintas ruang kerja yang diizinkan.
- [ ] Filter dan sorting berdasarkan status, prioritas, assignee, dan due date.
- [ ] Penyempurnaan List, Board, Calendar, dan Gantt dengan perilaku yang konsisten.
- [ ] Deskripsi Task yang nyaman dibaca dan diedit.
- [ ] Penanganan empty state, loading, dan error state.

**Kriteria fase:** pengguna dapat menemukan, memahami, memperbarui, dan memantau Task tanpa alur yang membingungkan.

# 6. Fase 3 — Collaboration & Notification

**Tujuan:** membuat perubahan dan tanggung jawab pekerjaan dapat diketahui oleh orang yang tepat.

- [ ] Pencatatan Activity otomatis untuk pembuatan dan perubahan Task.
- [ ] Inbox yang menampilkan event relevan, bukan sekadar daftar Task.
- [ ] Notifikasi assignment dan perubahan penting.
- [ ] Mention pengguna dan komentar/reply.
- [ ] Status sudah dibaca/belum dibaca.
- [ ] Agenda dengan data due date, upcoming, dan overdue yang nyata.
- [ ] Preferensi notifikasi dasar.

**Kriteria fase:** pengguna dapat mengetahui apa yang perlu diperhatikan dan mengapa suatu pekerjaan membutuhkan tindak lanjut.

# 7. Fase 4 — Productivity & Overview

**Tujuan:** memberi gambaran progres yang membantu pengambilan keputusan.

- [ ] Dashboard memakai data aktual dan definisi metrik yang jelas.
- [ ] Ringkasan pekerjaan per pengguna.
- [ ] Progress per Space dan List.
- [ ] Monitoring overdue dan pekerjaan berisiko.
- [ ] Tren penyelesaian pekerjaan.
- [ ] Favorites setelah aturan dan manfaatnya ditetapkan.
- [ ] Ringkasan produktivitas sederhana.

**Kriteria fase:** dashboard menjawab kondisi pekerjaan dan area yang membutuhkan perhatian, bukan hanya menampilkan angka.

# 8. Fase 5 — Integration & Data Portability

**Tujuan:** memudahkan perpindahan data dan mengurangi ketergantungan pada integrasi yang rapuh.

- [ ] Tetapkan ruang lingkup dan format import ClickUp.
- [ ] Simpan koneksi OAuth secara aman dan sesuai siklus hidup pengguna.
- [ ] Mapping Workspace, Space, Folder, List, Task, Member, dan Assignee.
- [ ] Mapping status, prioritas, due date, serta dependency jika didukung sumber.
- [ ] Preview sebelum import dan validasi data.
- [ ] Log hasil import, kegagalan, dan mekanisme retry yang aman.
- [ ] Cegah duplikasi saat import diulang.
- [ ] Evaluasi export data TaskFlow.

**Kriteria fase:** proses import dapat dipahami, diaudit, diulang dengan aman, dan tidak menghasilkan duplikasi diam-diam.

# 9. Fase 6 — Product Readiness

**Tujuan:** menyiapkan TaskFlow untuk penggunaan tim yang lebih luas.

- [ ] Penyempurnaan role/permission lanjutan di luar aturan Owner dan Member Space yang telah ditetapkan pada Fase 1.
- [ ] Pengelolaan profil dan preferensi pengguna.
- [ ] Audit trail untuk aktivitas penting.
- [ ] Backup dan prosedur pemulihan.
- [ ] Pemeriksaan performa query dan pagination.
- [ ] Hardening keamanan dan pemeriksaan dependensi.
- [ ] Dokumentasi pengguna dan administrator.
- [ ] Uji penggunaan bersama tim dan perbaikan berdasarkan umpan balik.

**Kriteria fase:** aplikasi dapat dioperasikan, dipelihara, dipulihkan, dan digunakan tim dengan kontrol akses yang jelas.

## 10. Hal yang Sengaja Belum Diprioritaskan

Fitur berikut tidak menjadi prioritas sampai kebutuhan nyata muncul:

- AI task assistant.
- Chat internal seperti aplikasi pesan.
- Workflow automation yang kompleks.
- Integrasi banyak aplikasi sekaligus.
- Whiteboard atau document collaboration skala besar.
- Fitur enterprise yang belum dibutuhkan.

Keputusan ini menjaga TaskFlow tetap fokus dan menghindari penambahan kompleksitas sebelum fondasi dan kebutuhan utamanya matang.

## 11. Aturan Pengelolaan Backlog

Gunakan status berikut untuk setiap item:

- **Planned** — sudah masuk roadmap, belum dikerjakan.
- **In Progress** — sedang dikerjakan.
- **Testing** — implementasi selesai, menunggu verifikasi.
- **Done** — kriteria penerimaan terpenuhi dan sudah diverifikasi.
- **Blocked** — tertahan oleh keputusan atau dependensi.

Setiap item backlog sebaiknya memiliki:
1. ID dan tujuan yang jelas.
2. Temuan/kebutuhan yang menjadi alasan perubahan.
3. Ruang lingkup dan file/area yang terdampak.
4. Kriteria penerimaan yang dapat diuji.
5. Catatan hasil test dan commit terkait.

**Catatan penting:** jangan mengubah status menjadi Done hanya karena kode sudah ditulis. Status Done memerlukan verifikasi sesuai kriteria penerimaan.

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
| Activity | Task creation dan Task note dicatat; feed menampilkan aktivitas dari Task pada Space yang dapat diakses | Implementasi dan uji akses Space tersedia |
| Settings | Halaman pengaturan dasar dan ClickUp | Sebagian konten masih placeholder |
| ClickUp | OAuth connect/callback | OAuth tersedia; alur import end-to-end belum terverifikasi |

### Catatan arsitektur dari audit

- Workspace ID `1` memang ditetapkan sebagai wadah data internal TaskFlow, bukan konteks kerja atau batas otorisasi pengguna. Akses pengguna harus ditentukan pada tingkat Space.
- Route detail Task sempat ditemukan didefinisikan dua kali; definisi duplikat sudah dihapus dan masuk pengujian route.
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

- [x] **F1-01 — Audit konteks Workspace aktif**
  - Telusuri seluruh alur pemilihan Workspace dan relasi user–Workspace.
  - Pastikan query Task, Space, Folder, List, dan anggota selalu dibatasi pada Workspace yang memang boleh diakses.
  - Pertahankan Workspace ID `1` sebagai wadah internal; jangan membangun mekanisme multi-Workspace atau mengganti scope ini tanpa keputusan baru.
  - Pastikan Workspace tidak lagi ditawarkan sebagai konteks kerja yang dapat dipilih/dikelola pengguna. Penegakan akses data berdasarkan keanggotaan dan kepemilikan Space dilaksanakan pada F1-02.
  - Kriteria penerimaan: Workspace tetap menggunakan ID `1` sebagai wadah internal dan route manajemen/pemilihan Workspace legacy tidak tersedia; otorisasi Space dilanjutkan dan diuji pada F1-02.

  - **Status audit: Done (8 November 2026).** Audit source dan koreksi minimum F1-01 telah dilakukan serta diverifikasi melalui GitHub Actions. Penegakan akses per Space tetap menjadi ruang lingkup implementasi dan pengujian F1-02.
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
  - **Hasil pengujian otomatis:** GitHub Actions run `42402205140` berstatus **success**; `php artisan test` menghasilkan **4 passed (15 assertions)**, termasuk dua pengujian `WorkspaceContextTest` untuk memastikan route Workspace legacy tidak terdaftar dan route Folder tetap tersedia.
  - **Commit teruji:** `f156698` (test suite hijau setelah koreksi test dashboard). Workflow dan roadmap juga telah dikoreksi/diperbarui pada commit setelahnya.
  - **Status:** Done — ruang lingkup F1-01 selesai dan lulus pengujian otomatis. Pembatasan akses berdasarkan Space tetap dikerjakan pada F1-02; tidak termasuk penutupan F1-01.

- [x] **F1-02 — Audit otorisasi objek**
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

  - **Status audit: In Progress (8 November 2026).** Audit awal source F1-02 telah dimulai pada branch `main`. Belum ada perubahan kode atau database pada tahap audit ini.
  - **Temuan audit awal (8 November 2026):**
    - `spaces.owner_id` sudah memiliki kolom melalui migration, tetapi kolom dibuat nullable dan tidak memiliki foreign key ke `users`.
    - `Space::$fillable` belum memuat `owner_id`. Padahal `SpaceController::store()` mengirim `owner_id = auth()->id()`; akibat mass assignment, Owner berisiko tidak tersimpan.
    - `SpaceController::index()` mengambil seluruh Space pada Workspace ID 1 tanpa filter Owner/Member. `show()` hanya memeriksa Workspace ID 1.
    - `SpaceController::authorizeSpace()` masih mengizinkan Space dengan `owner_id = null` untuk diubah/dinonaktifkan/dihapus oleh user terautentikasi mana pun. Ini tidak sesuai matriks hak Owner.
    - Belum ditemukan migration/model/relasi khusus `space_members`. Keanggotaan Space belum memiliki sumber data sendiri.
    - `TaskManagerController::authorizeTask()` baru memeriksa `workspace_id = 1` dan session Workspace; belum memeriksa membership Space, `created_by`, atau `task_assignees`. Akibatnya, endpoint update/delete/status/assignment/Subtask/Dependency yang mengandalkan method ini belum menerapkan matriks hak.
    - Query Dashboard/List/Board/Calendar/Gantt dan `listPage()` masih membatasi data berdasarkan Workspace/Space yang diminta, bukan daftar Space yang boleh diakses user.
    - Pembuatan Task memeriksa List dan Space dalam Workspace 1, tetapi belum memastikan pembuat merupakan Owner/Member Space.
    - Assignment saat ini memeriksa `workspace_members`, bukan keanggotaan Space; ini bertentangan dengan keputusan bahwa assignment hanya kepada anggota Space terkait.
    - `FolderController::authorizeWorkspace()` masih bergantung pada `workspace_members`, sedangkan `TaskListController::authorizeSpace()` hanya memeriksa Workspace ID 1. Otorisasi Folder dan List belum konsisten dengan Space.
    - Tabel/model `members` adalah relasi global `user_id` dengan `clickup_user_id` dan dipakai oleh `MemberController`; tabel ini tidak boleh dialihfungsikan menjadi pivot keanggotaan Space.
    - Route yang diperiksa belum menunjukkan endpoint untuk menambahkan note pada Task. Hak assignee untuk menambahkan note perlu menunggu audit/implementasi fitur note; jangan memberikan hak edit detail sebagai pengganti.
    - Dependency memanggil `authorizeTask()` pada kedua Task yang terhubung dan memeriksa keduanya berada di Space yang sama jika `space_id` terisi. Namun, karena `authorizeTask()` belum menerapkan akses Space/role, batas otorisasinya belum cukup.
    - Reorder memvalidasi Task berada pada context Workspace/Space/List yang dikirim, tetapi belum memeriksa hak Owner/pembuat. Operasi ini harus mengikuti hak edit Task dan tidak boleh menjadi jalur perubahan status bagi assignee-only di luar aturan.
  - **Implikasi dan urutan implementasi yang perlu dipertahankan:** mulai dari fondasi Owner dan relasi Space, lanjut pivot `space_members`, manajemen anggota Space, helper/policy otorisasi Task, lalu pengamanan Folder/List, Task, Subtask, Dependency, Assignment, dan Reorder. Tambahkan feature test untuk setiap kombinasi Owner, Member pembuat, Member assignee-only, serta user tanpa akses.
  - **Catatan data:** sesuai keputusan sebelumnya, belum ada data Space lama yang perlu di-backfill. Space baru harus menyimpan Owner pembuatnya dan langsung mencatat Owner tersebut sebagai anggota awal pada `space_members`; tidak ada anggota lain yang ditambahkan otomatis. Workspace tetap ID 1.
  - **Implementasi fondasi Owner dan membership (8 November 2026):**
    - Menambahkan `owner_id` ke `Space::$fillable`, sehingga Owner yang dikirim `SpaceController::store()` sekarang dapat tersimpan melalui mass assignment.
    - Menambahkan relasi `Space::owner()`, `Space::members()`, dan `Space::memberships()`.
    - Menambahkan relasi `User::ownedSpaces()`, `User::spaces()`, dan `User::spaceMemberships()`. Pada eksekusi CI pertama, test menemukan relasi tersebut belum masuk ke file `User.php` karena pola pembaruan tidak cocok dengan indentasi file. Relasi kemudian ditambahkan secara eksplisit pada commit `6b168af`; Pengujian ulang pada GitHub Actions run `42402774106` berhasil: **6 test passed (21 assertions)**.
    - Menambahkan model `SpaceMember` dengan relasi Space, User, dan inviter.
    - Menambahkan migration `2026_11_08_000001_create_space_members_table` dengan foreign key ke Space/User, unique pair `space_id + user_id`, serta `invited_by` nullable. Tabel `members` global tidak diubah.
    - Menambahkan migration `2026_11_08_000002_add_owner_foreign_key_to_spaces_table`; penghapusan User pemilik Space dibatasi oleh foreign key `RESTRICT` agar Space tidak kehilangan Owner tanpa sengaja.
    - Menambahkan migration `2026_11_08_000003_make_space_owner_required`; kolom `spaces.owner_id` sekarang wajib terisi. Sesuai keputusan, tidak diperlukan backfill karena tidak ada data Space lama.
    - Menambahkan `SpaceMembershipFoundationTest` untuk mass assignment Owner, relasi Owner, keanggotaan, dan inviter.
  - **Implementasi manajemen anggota Space (8 November 2026):** menambahkan `SpaceMemberController` dan halaman `spaces.members` untuk Owner melihat daftar anggota, menambahkan pengguna, dan mengeluarkan anggota. Route GET/POST/DELETE berada dalam middleware auth. Endpoint memverifikasi Owner secara eksplisit dan Workspace ID 1. Daftar Space mendapat tautan Anggota. `SpaceController::authorizeSpace()` diperketat agar hanya Owner eksplisit yang dapat mengubah, menonaktifkan, atau menghapus Space.
  - **Implementasi visibilitas Space (8 November 2026):**
    - Menambahkan scope `Space::accessibleTo()` dan pemeriksaan `isAccessibleBy()` berdasarkan Owner atau relasi `space_members`.
    - Daftar Space dan akses detail Space hanya menampilkan/menerima Owner atau Member.
    - Saat Space dibuat, Owner disimpan sebagai `owner_id` sekaligus dicatat sebagai anggota awal di `space_members` dalam satu transaksi.
    - Sidebar navigasi hanya menampilkan Space yang dapat diakses user. CI pertama menemukan query sidebar masih menampilkan seluruh Space; query diperbaiki dan diuji ulang.
    - Dashboard, query List/Board/Calendar/Gantt, selector List, dan halaman List dibatasi ke Space aktif yang dapat diakses user.
    - Folder dan List dapat dilihat Owner/Member; perubahan struktur Folder/List dibatasi untuk Owner.
  - **Pengujian:** `SpaceMembershipFoundationTest`, `SpaceMemberManagementTest`, dan `SpaceVisibilityTest` ditambahkan. GitHub Actions run `42403575394` berstatus **success**: **14 test passed (46 assertions)**. Pengujian mencakup filter daftar/sidebar Space, akses detail, hak lihat Folder/List untuk Member, pembatasan perubahan struktur kepada Owner, dan pencatatan Owner sebagai anggota awal.
  - **Implementasi otorisasi Task (8 November 2026):**
    - `authorizeTask()` sekarang memastikan Task berada di Workspace ID 1, Space aktif, dan user adalah Owner/anggota Space.
    - Hak pengelolaan detail Task, penghapusan, assignment, Subtask, Dependency, dan Reorder dibatasi untuk Owner Space atau pembuat Task (`created_by`).
    - Assignee-only hanya dapat mengubah status melalui endpoint status khusus dan menambahkan note; tidak dapat mengubah detail, menghapus Task, mengubah PIC, membuat/mengubah Subtask atau Dependency, maupun mengubah urutan/kolom melalui Reorder.
    - Member Space dapat membuat Task pada List di Space yang dapat diaksesnya; pencatat `created_by` tetap user pembuat.
    - Assignment sekarang hanya menerima pengguna yang terdaftar sebagai anggota Space terkait, bukan sekadar anggota Workspace.
    - Endpoint note menggunakan tabel `activities` dengan action `task_note`; note tampil di detail Task, hanya dapat ditambahkan oleh Owner/pembuat/assignee, dan tidak menyediakan edit/hapus note.
    - `ActivityController` sebelumnya menampilkan aktivitas seluruh Workspace ID 1 tanpa filter Space. Query feed sekarang dibatasi ke Task pada Space yang dapat diakses user; pengujian lintas-Space ditambahkan pada `ActivityVisibilityTest`.
    - Detail Task mengirim permission flags ke UI. Modal menampilkan kontrol sesuai hak: Owner/pembuat mengelola detail; assignee-only mengubah status dan mengirim note; member biasa hanya melihat detail serta note yang tersedia.
  - **Pengujian otorisasi dan Activity:** `TaskAuthorizationTest` mencakup pembuatan Task oleh Member, izin status dan note assignee-only, penolakan edit/hapus/assignment/Subtask/Dependency/Reorder untuk assignee-only, hak pembuat dan Owner, penolakan user luar Space, serta batas Assignment ke anggota Space. `ActivityVisibilityTest` memastikan feed tidak membocorkan aktivitas Task dari Space lain. `SpaceMemberManagementTest` memastikan Owner tidak dapat mengeluarkan dirinya sendiri dari membership. GitHub Actions run `42405067475` berstatus **success**: **23 test passed (86 assertions)**.
  - **Status F1-02: Done pada source code dan CI (8 November 2026).** Seluruh matriks role Space/Task yang disepakati telah diterapkan dan diuji otomatis. Migration belum dijalankan pada VPS produksi; deployment menunggu keputusan/permintaan terpisah. Tidak ada perubahan konfigurasi VPS.
  
- [x] **F1-03 — Rapikan definisi route**
  - Audit memastikan dua definisi GET `/tasks/{task}` menuju controller/method yang sama, memakai middleware `auth` yang sama, dan memiliki nama route identik. Satu definisi duplikat dihapus; definisi tersisa tetap diletakkan setelah route Task statis.
  - Pemeriksaan source terhadap 51 route bernama tidak menemukan duplikasi kombinasi method+URI atau nama route.
  - `RouteDefinitionTest` memastikan GET `tasks/{task}` terdaftar tepat satu kali dengan nama `tasks.show`.
  - **Status F1-03: Done (8 November 2026).** GitHub Actions run `42405067475` berstatus **success**: **23 test passed (86 assertions)**.

- [x] **F1-04 — Bangun pengujian alur inti**
  - `AuthFlowTest`: registrasi, login, logout, dan penolakan kredensial yang salah.
  - `TaskAuthorizationTest`: matriks Owner/creator/assignee/member/outsider, status, note, assignment, serta larangan akses yang tidak sesuai.
  - `TaskWorkflowTest`: create Task oleh Member, lifecycle Subtask, lifecycle Dependency, larangan Dependency lintas-Space, Reorder, dan larangan pembuatan Task oleh user tanpa akses Space.
  - `SpaceStructureAuthorizationTest`, `SpaceMemberManagementTest`, `SpaceMembershipFoundationTest`, `SpaceVisibilityTest`, `ActivityVisibilityTest`, `RouteDefinitionTest`, dan `WorkspaceContextTest` menguji fondasi Space, pembatasan Folder/List, akses data, Activity Feed, serta route.
  - GitHub Actions berjalan otomatis pada push ke `main` dan Pull Request menuju `main`; tidak memerlukan PHPUnit pada VPS produksi. Workflow juga menjalankan `node --check public/js/task-manager.js` untuk memvalidasi sintaks JavaScript.
  - **Hasil CI akhir:** run `42405736939` berstatus **success**: **35 test passed (139 assertions)**; langkah pemeriksaan JavaScript juga sukses.
  - **Status F1-04: Done (8 November 2026).** Skenario feature test inti berjalan otomatis dan dapat diulang. Pengujian browser end-to-end visual belum termasuk ruang lingkup fase ini.

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
- Otorisasi Subtask, Dependency, dan Reorder mengikuti hak pengelolaan Task induk; assignee-only tidak dapat memakai endpoint tersebut untuk mengubah detail atau urutan Task.
- Endpoint note menggunakan tabel `activities` dengan action `task_note`; Owner/pembuat/assignee dapat menambahkan note pada Task yang dapat diaksesnya. Note ditampilkan pada detail Task dan tidak memiliki aksi edit/hapus.
### P1 — Penting setelah P0

- [ ] **F1-05 — Audit validasi dan konsistensi request**
  - Periksa validasi input pada endpoint utama.
  - Pastikan respons sukses/gagal konsisten dan kesalahan tidak membocorkan detail sensitif.
  - Kriteria penerimaan: input tidak valid ditolak dengan pesan yang layak dan data tidak berubah sebagian.
  - **Status: In Progress (8 November 2026).** Audit dan koreksi awal sudah berjalan pada branch `main`; belum seluruh endpoint dan skenario request selesai diaudit.
  - **Temuan audit awal:**
    - Redirect Task create/update/delete telah memakai `safeReturnUrl()`, memvalidasi tipe/panjang input, dan menolak URL eksternal, skema tidak didukung, karakter ambigu, serta authority yang tidak sesuai host/port aplikasi.
    - Endpoint pembuatan Subtask masih memakai `return_url` mentah untuk redirect. Ini membuka jalur open redirect yang belum tertutup oleh pengamanan Task utama.
    - Endpoint Reorder memperbarui Task satu per satu. Jika request berisi beberapa Task dan otorisasi Task berikutnya gagal setelah Task sebelumnya terlanjur diperbarui, perubahan sebelumnya dapat tertinggal (partial update).
    - Validasi tanggal Task utama sudah menolak `due_date` yang lebih awal dari `start_date`.
    - Payload Reorder sudah menolak ID Task duplikat.
    - Folder sudah membuat slug unik per Space untuk menghindari benturan slug saat nama Folder sama.
    - Redirect OAuth ClickUp mengarah ke endpoint provider tetap; redirect internal lain yang diperiksa menggunakan named route/back redirect, bukan URL bebas dari request.
  - **Koreksi yang sudah diterapkan:**
    - `TaskManagerController::storeSubtask()` sekarang memvalidasi `return_url` sebagai string maksimal 2048 karakter dan menggunakan `safeReturnUrl()` dengan fallback ke `tasks.index`.
    - Seluruh proses update pada `TaskManagerController::reorder()` sekarang dibungkus transaksi database agar kegagalan otorisasi/operasi pada satu Task membatalkan seluruh perubahan Reorder dalam request tersebut.
    - `MemberController::searchUsers()` sekarang memvalidasi parameter `q` sebagai string maksimal 100 karakter sebelum menjalankan `trim()`; endpoint tidak lagi memproses tipe input yang salah secara langsung.
    - Validasi `MemberController::store()` sekarang mensyaratkan `user_id` berupa integer selain harus merujuk ke User yang ada dan belum terdaftar.
    - `SettingsController::clickupCallback()` sekarang menolak authorization code yang bukan string, kosong, atau lebih dari 2048 karakter sebelum menghubungi ClickUp.
    - Respons OAuth ClickUp hanya disimpan ke session jika `access_token` berupa string non-kosong dengan panjang maksimal 4096 karakter. Log kegagalan tidak lagi menyimpan seluruh body respons provider.
  - **Pengujian tambahan:**
    - `TaskWorkflowTest` menguji penolakan redirect eksternal pada pembuatan Subtask.
    - `TaskWorkflowTest` menguji input `return_url` bertipe array ditolak sebelum Subtask dibuat.
    - `TaskWorkflowTest` menguji Reorder rollback seluruh perubahan jika satu Task dalam payload tidak boleh dikelola pengguna.
    - `MemberRequestValidationTest` menguji input pencarian anggota bertipe array dan `user_id` bukan integer ditolak tanpa menambah data.
    - `ClickUpOAuthValidationTest` menguji authorization code bertipe array tidak diteruskan ke provider dan respons tanpa access token valid tidak disimpan ke session.
    - Pengujian sebelumnya tetap mencakup URL eksternal pada Task create/update/delete, tipe input return URL tidak valid, tanggal Task, ID Reorder duplikat, serta slug Folder duplikat.
  - **Commit terkait F1-05:** `68b867e`, `c3d0a10`, `e3b1e1f`, `8e180c4`, `29b8d22`, `2084073`, `d8980e9`, `7da590e`, `37ffb05`, `8a46d2c`, `f371f21`, dan `ec46dd0`.
  - **Hasil CI:** baseline sebelum koreksi terakhir, run `42406417459`, sukses dengan **39 test passed (159 assertions)**. Setelah koreksi Subtask/Reorder, run `42407983588` sukses dengan **42 test passed (168 assertions)**. Koreksi validasi Member pada run `42408158095` sukses dengan **42 test passed (168 assertions)**; setelah dua pengujian Member ditambahkan, run `42408170805` sukses dengan **44 test passed (174 assertions)**. Koreksi ClickUp OAuth pada run `42408313664` sukses dengan **44 test passed (174 assertions)**; setelah dua pengujian OAuth ditambahkan, run `42408332560` sukses dengan **46 test passed (183 assertions)**. Pemeriksaan sintaks JavaScript juga sukses. Dua run di antara commit awal dan koreksi referensi transaksi sempat gagal karena kesalahan sintaks pada penulisan namespace facade; diperbaiki pada commit `7da590e` dan diverifikasi ulang melalui run sukses.
  - **Batas audit berikutnya:** lanjutkan pemeriksaan validasi/response pada endpoint Task, Subtask, Dependency, Space, Folder, List, Settings/ClickUp OAuth, dan autentikasi; verifikasi batas panjang/format field terhadap kebutuhan serta tipe kolom database; cek konsistensi respons validasi untuk request browser dan JSON. Jangan menandai F1-05 Done sebelum seluruh temuan ditutup dan CI akhir sukses.

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

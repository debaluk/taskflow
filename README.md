# TaskFlow

TaskFlow adalah aplikasi manajemen pekerjaan berbasis Laravel dengan konsep task management yang terinspirasi dari ClickUp, tetapi memiliki arsitektur, database, authorization, ownership, assignment, dan workflow internal sendiri.

**Status:** Active Development

---

## 1. Project Overview

TaskFlow digunakan untuk mengelola pekerjaan melalui struktur:

    Workspace
    └── Space
        ├── Folder
        │   └── List
        │       └── Task
        │           └── Subtask
        └── List
            └── Task
                └── Subtask

Workspace tetap berada pada database, tetapi tidak menjadi node utama pada UI.

Konsep UI utama:

    Space
    └── List
        └── Task
            └── Subtask

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel |
| PHP | 8.3+ |
| Database | MariaDB 10.6+ |
| ORM | Eloquent |
| Frontend | Laravel Blade |
| CSS | Modular CSS |
| JavaScript | Vanilla JavaScript |
| Build Tool | Vite |
| Package Manager | Composer + npm |
| Authentication | Laravel Authentication |
| Session | Database |
| Web Server | Apache |
| PHP Runtime | PHP-FPM 8.3 |

TaskFlow menggunakan pendekatan Laravel Monolith:

    Laravel
    ├── Blade
    ├── Modular CSS
    ├── Vanilla JavaScript
    └── MariaDB

---

## 3. Application Architecture

    Browser
       |
       v
    Routes
       |
       v
    Middleware
       |
       v
    Controllers
       |
       +-----------------------+
       |                       |
       v                       v
    Authorization        Business Logic
       |                       |
       +-----------+-----------+
                   |
                   v
              Eloquent
                   |
                   v
                MariaDB

---

## 4. Core Architecture

Struktur data:

    Workspace
       |
       +-- Space
             |
             +-- Folder
             |     |
             |     +-- List
             |
             +-- List
                   |
                   +-- Task
                         |
                         +-- Subtask

Security dan data access:

    User
     |
     +-- Ownership
     |
     +-- Member
           |
           +-- Assignment
                  |
                  +-- Task

---

## 5. Workspace

Workspace dipertahankan sebagai wadah teknis internal. Aplikasi saat ini menggunakan Workspace ID 1; Workspace bukan konteks kerja yang dipilih pengguna dan bukan sumber otorisasi pengguna.

Aturan:
- workspace_id tetap disimpan pada data yang memerlukannya untuk menjaga relasi dan kompatibilitas.
- Akses pengguna ditentukan oleh Owner dan keanggotaan pada Space.
- Route manajemen/pemilihan Workspace legacy tidak tersedia pada alur aplikasi.
- Jangan menghapus Workspace ID 1 atau mengubah arsitektur ini tanpa keputusan baru.

## 6. Space

Space adalah area kerja utama yang ditampilkan pada sidebar.

Space harus dinamis.

Contoh seperti:

- IPAS v2
- WIPAS v2

hanyalah data contoh.

Tidak boleh ada logic aplikasi yang bergantung pada nama Space tertentu.

Space mempunyai:

- workspace context;
- owner;
- List;
- optional Folder;
- Task.

---

## 7. Folder

Folder adalah container opsional di dalam Space. Folder belum menjadi bagian wajib dari alur kerja harian.

Aturan saat ini:
- List dapat berada langsung di Space atau di dalam Folder.
- Folder dan List harus berasal dari Space yang sama.
- Mengubah status aktif Folder tidak otomatis menonaktifkan List di dalamnya; semantik tersebut ditunda sampai Folder benar-benar digunakan dalam operasional.
- Folder tidak dapat dihapus selama masih memiliki Subfolder, List, atau Task aktif.

## 8. List

List merupakan container Task.

    Space
    └── List
        ├── Task
        ├── Task
        └── Task

List dapat:

- berada langsung di Space;
- berada di Folder;
- menjadi context aktif saat membuat Task.

Jika user sedang berada pada sebuah List, Task baru otomatis menggunakan List tersebut.

Todo modal tidak perlu meminta user memilih List lagi apabila context List sudah diketahui.

---

## 9. Task

Task adalah unit pekerjaan utama.

Task dapat mempunyai:

- title;
- description;
- status;
- priority;
- due date;
- owner;
- assignee;
- workspace;
- space;
- folder;
- list;
- parent task;
- dependency;
- activity.

Tabel utama:

    tasks

---

## 10. Subtask

Subtask tetap menggunakan tabel tasks dengan relasi parent_id dan dapat memiliki Subtask bertingkat.

Aturan:
- Subtask tidak otomatis mengubah due date parent.
- Progres parent dihitung dari Subtask aktif selama masih ada.
- Jika seluruh Subtask aktif dihapus, progres parent dipertahankan; tidak otomatis dikembalikan ke nol.
- Menghapus Task atau Subtask melakukan soft-delete terhadap seluruh keturunan Subtask secara rekursif dalam satu transaksi.
- Menghapus Subtask tidak menghapus parent di atasnya.

## 11. Ownership

Ownership utama pada tingkat area kerja berada di Space.

- Setiap Space wajib memiliki owner_id yang mengacu ke users.id.
- Owner Space otomatis menjadi anggota awal pada space_members.
- Owner dapat melihat dan mengelola seluruh Task pada Space miliknya, serta mengelola anggota dan pengaturan Space.
- Member Space dapat melihat Task dan membuat Task baru.
- Pembuat Task dapat mengubah detail dan menghapus Task yang dibuatnya.
- Owner tidak di-hardcode; Owner berasal dari pengguna yang membuat Space atau business rule yang sah.

## 12. Authorization

Authorization harus dilakukan di backend.

Flow:

    Request
      |
      v
    Authenticated User
      |
      v
    Authorization
      |
      +---- Denied
      |
      v
    Query Filtering
      |
      v
    Data

Menyembunyikan tombol pada UI bukan authorization.

Backend tetap harus melakukan pemeriksaan akses.

---

## 13. Data Filtering

Query aplikasi harus dibatasi pada Space yang dapat diakses pengguna.

    Logged-in User
          |
          v
    Owned Space / space_members
          |
          v
    Authorized Space
          |
          v
    Authorized List
          |
          v
    Authorized Task

Workspace ID 1 hanya menjadi wadah teknis internal. Jangan menggunakan workspace_members atau members global sebagai pengganti keanggotaan Space.

## 14. Members

Tabel members adalah direktori anggota global dan mapping identitas ClickUp. Direktori ini bukan sumber otorisasi Space dan bukan sumber eligibility assignment.

- Pengguna terautentikasi dapat membaca/mencari direktori sesuai route aplikasi.
- Penambahan/penghapusan anggota global menunggu Role Management dan Administrator Sistem pada Fase 6.
- Owner Space tidak mengelola direktori global; Owner hanya mengelola anggota Space melalui space_members.

## 15. Members Table

Struktur utama:

    members
    ├── id
    ├── user_id
    ├── invited_by
    ├── clickup_user_id
    ├── created_at
    └── updated_at

Tabel ini mempertahankan kebutuhan direktori dan integrasi ClickUp. Relasi keanggotaan Space disimpan terpisah pada space_members.

## 16. Member Routes

Route direktori global:

    GET    /members
    POST   /members
    DELETE /members/{member}
    GET    /members/search-users

Route baca tersedia bagi pengguna terautentikasi. Saat ini route tambah/hapus masih dibatasi middleware auth saja; pembatasan ke Administrator Sistem belum ditegakkan karena Role Management belum tersedia. Jangan menganggap mutasi direktori global sudah memiliki pembatasan Administrator Sistem. Implementasinya ditunda dan dicatat pada roadmap Fase 6.

Keanggotaan Space dikelola melalui:

    GET    /spaces/{space}/members
    POST   /spaces/{space}/members
    DELETE /spaces/{space}/members/{user}

Hanya Owner Space yang boleh mengelola anggota Space.

## 17. Assignment

Assignment hanya boleh diberikan kepada Owner atau pengguna yang tercatat sebagai anggota Space terkait pada space_members.

    Space
      |
      v
    space_members
      |
      v
    Eligible Assignee
      |
      v
    task_assignees
      |
      v
    Task

Direktori members global tidak memberikan hak assignment. Backend memvalidasi keanggotaan Space pada setiap perubahan assignment.

## 18. Task Assignees

Assignment disimpan pada tabel task_assignees dengan pasangan unik task_id + user_id dan foreign key ke Task/User. Struktur database mendukung relasi banyak assignee, sementara endpoint assignment saat ini mengatur satu PIC atau mengosongkannya.

## 19. My Tasks

My Tasks berarti:

> Task yang ditugaskan kepada logged-in user.

Bukan:

- Task yang dibuat user;
- Task yang dimiliki user.

Flow:

    Logged-in User
          |
          v
    Task Assignees
          |
          v
    My Tasks

---

## 20. Tasks Navigation

Menu Tasks merupakan satu menu dengan beberapa tab:

    Tasks
    ├── All Tasks
    ├── My Tasks
    ├── Overdue
    └── Completed

Tidak membuat menu My Tasks terpisah pada sidebar.

---

## 21. Sidebar

Struktur sidebar saat ini:

    Dashboard
    Agenda
    Tasks
    Inbox

    WORKSPACES

    Space
    ├── List
    ├── List
    └── List

    Space
    ├── List
    └── List

Workspace bukan node clickable utama pada sidebar.

Sidebar tidak menampilkan Task.

Settings diakses melalui avatar/user menu.

Favorites sementara:

    HIDDEN / PENDING

Jangan implement Favorites sebelum diminta.

---

## 22. Space Navigation

Space mempunyai tombol + untuk membuat Space.

Setiap Space mempunyai tombol + untuk membuat List.

Klik Space:

    menampilkan seluruh Task pada Space.

Klik List:

    menampilkan Task pada List tersebut.

---

## 23. Task Creation

Pembuatan Task mengikuti context saat ini.

Jika user berada pada:

    Space A
    └── List A

dan membuat:

    Task Baru

maka:

    space_id = Space A
    list_id  = List A

Tidak perlu memilih context yang sama lagi melalui modal.

---

## 24. Task Views

TaskFlow mendukung beberapa cara melihat Task:

    List
    Board
    Calendar
    Gantt

View adalah cara menampilkan Task.

View bukan primary creation entry point.

Task yang sama dapat ditampilkan melalui beberapa View.

---

## 25. Dashboard

Dashboard merupakan halaman overview.

Komponen utama:

    Dashboard
    ├── Ringkasan Hari Ini
    ├── Pekerjaan Saya
    ├── Perhatian
    ├── Space Anda
    └── Aktivitas Terbaru

Dashboard berfungsi sebagai overview pekerjaan.

---

## 26. Agenda

Agenda direncanakan menampilkan pekerjaan berdasarkan due date, tetapi controller saat ini masih mengirim collection kosong dan nilai hitung nol. Halaman/route tersedia sebagai placeholder, bukan fitur operasional.

## 27. Inbox

Inbox direncanakan untuk item yang membutuhkan perhatian user. Controller saat ini mengirim collection kosong dan unreadCount nol. Halaman/route tersedia sebagai placeholder, belum menjadi sistem notifikasi operasional.

## 28. Favorites

Favorites saat ini:

    PENDING

dan sementara:

    HIDDEN

Jangan implement atau tampilkan kembali Favorites sampai ada instruksi eksplisit.

---

## 29. Activity

Activity Feed saat ini menampilkan aktivitas Task pada Space yang dapat diakses pengguna. Implementasi yang tersedia mencatat pembuatan Task dan note Task; belum semua perubahan Task, assignment, status, atau perubahan keanggotaan menghasilkan event audit otomatis.

Activity belum dapat dianggap sebagai audit trail lengkap.

## 30. Database Architecture

Database dikonfigurasi melalui environment DB_*; nama database tidak di-hardcode pada source.

Tabel utama:
- users, workspaces, spaces, space_members
- folders, task_lists, tasks
- task_assignees, task_dependencies, activities
- members sebagai direktori global

workspace_members dipertahankan sebagai relasi legacy/kompatibilitas dan bukan sumber otorisasi Space.

## 31. Database Relationships

    users
     |
     +-- members (direktori global)
     |
     +-- space_members
              |
              v
            spaces
              |
              +-- folders
              |     |
              |     +-- task_lists
              |
              +-- task_lists
                    |
                    v
                   tasks
                    |
                    +-- parent_id (Subtask)
                    +-- task_assignees
                    +-- task_dependencies
                    +-- activities (polymorphic)

Owner Space juga direferensikan oleh spaces.owner_id. Keanggotaan dan assignment tetap terpisah.

## 32. Database Rules

### Workspace ID

workspace_id tetap dipertahankan.

Jangan menghapus workspace_id hanya karena Workspace tidak tampil pada UI.

### Owner

Jangan menggunakan owner_id = 1 sebagai default global.

Owner harus berasal dari authenticated user atau business rule.

### Parent Task

Subtask menggunakan parent_id pada tasks.

### Migration

Migration merupakan source of truth untuk perubahan schema.

Jangan mengedit migration lama sembarangan setelah migration digunakan.

Gunakan migration baru untuk perubahan schema.

---

## 33. Models

Model utama:
- Activity
- Folder
- Member
- Space
- SpaceMember
- Task
- TaskDependency
- TaskList
- User
- Workspace
- WorkspaceMember (legacy)

Relasi model harus konsisten dengan foreign key dan keputusan otorisasi Space.

## 34. Controllers

Controller aktif pada route aplikasi mencakup:
- ActivityController
- AuthController
- FolderController
- MemberController
- SettingsController
- SpaceController
- SpaceMemberController
- TaskListController
- TaskManagerController

Controller Workspace lama tetap berada di source bila masih dibutuhkan kompatibilitas, tetapi route manajemen/pemilihan Workspace tidak aktif.

## 35. Views

Struktur view utama:

    resources/views/
    ├── dashboard
    ├── tasks
    ├── agenda
    ├── inbox
    ├── members
    ├── settings
    ├── spaces
    ├── task-lists
    ├── folders
    ├── board
    ├── calendar
    ├── gantt
    ├── layouts
    └── workspaces

Legacy view tidak boleh dihapus sembarangan sebelum dependency diperiksa.

---

## 36. CSS Architecture

CSS dipisahkan berdasarkan tanggung jawab.

    public/css/
    ├── base.css
    ├── auth.css
    ├── components.css
    ├── task-manager.css
    ├── board.css
    ├── calendar.css
    ├── gantt.css
    ├── activity.css
    ├── agenda.css
    ├── assignee.css
    ├── datatables.css
    ├── layout.css
    ├── modal.css
    ├── responsive.css
    ├── sidebar.css
    ├── subtasks.css
    ├── tasks.css
    ├── taskflow.css
    ├── user-menu.css
    └── workspace.css

Prinsip:

    Base
      |
    Components
      |
    Feature / Page CSS

Hindari CSS inline untuk style permanen.

---

## 37. JavaScript

TaskFlow menggunakan Vanilla JavaScript.

JavaScript digunakan untuk UI behavior.

Backend digunakan untuk:

- Security;
- Authorization;
- Business Rule;
- Data Validation.

Jangan menggunakan JavaScript sebagai pengganti authorization backend.

---

## 38. ClickUp Integration

Integrasi ClickUp saat ini menyediakan alur OAuth connect/callback dasar. Access token hasil callback disimpan pada session, belum menjadi koneksi persisten per pengguna.

Import end-to-end belum siap dan tidak boleh digunakan untuk migrasi data produksi. Pengembangan import yang aman menjadi backlog Fase 5.

## 39. ClickUp Mapping

Field members.clickup_user_id disiapkan untuk mapping identitas direktori global dengan user ClickUp. Mapping ini tidak memberikan keanggotaan Space atau hak assignment; otorisasi tetap memakai space_members.

## 40. Import Data

Command legacy yang dipertahankan untuk pengembangan berikutnya:

    php artisan task:import-ipas <file.csv>

Command ini belum siap digunakan. Audit menemukan proses reset data dilakukan sebelum validasi CSV dan belum memenuhi kewajiban Owner serta space_members pada Space hasil import. Jangan menjalankannya pada database produksi. Perbaikan command menjadi backlog Fase 5.

## 41. Development Rules

### Rule 1 - No Hardcoded Owner

Jangan:

    $ownerId = 1;

### Rule 2 - Member Only Assignment

Assignment hanya melalui Member.

### Rule 3 - Workspace Remains

workspace_id tetap dipertahankan.

### Rule 4 - Dynamic Data

Jangan hardcode IPAS v2 atau WIPAS v2 sebagai requirement aplikasi.

### Rule 5 - Backend Authorization

Hidden UI bukan security.

### Rule 6 - Migration

Migration adalah source of truth schema.

### Rule 7 - Preserve Migration History

Perubahan schema dibuat melalui migration baru.

### Rule 8 - No Unnecessary JavaScript

JavaScript dikerjakan bila memang diperlukan.

### Rule 9 - No Premature Features

Jangan mengerjakan fitur yang belum menjadi prioritas.

---

## 42. Development Workflow

Workflow umum:

    Requirement
        |
        v
    Architecture
        |
        v
    Database / Migration
        |
        v
    Model / Relationship
        |
        v
    Authorization
        |
        v
    Controller
        |
        v
    View
        |
        v
    CSS
        |
        v
    JavaScript
        |
        v
    Test
        |
        v
    Git

Untuk perubahan besar:

    Database
    -> Backend
    -> Authorization
    -> UI
    -> CSS
    -> Test
    -> Commit

---

## 43. Useful Laravel Commands

Install dependency:

    composer install
    npm ci

Build asset bila perubahan menggunakan Vite:

    npm run build

Development:

    php artisan serve
    npm run dev

Migration dan pemeriksaan:

    php artisan migrate:status
    php artisan migrate

Pengujian otomatis:

    php artisan test

Pada produksi, backup database sebelum migration dan gunakan php artisan migrate --force. Jangan menjalankan migrate:fresh atau migrate:refresh pada database produksi.

## 44. Git Workflow

Periksa status:

    git status

Periksa perubahan:

    git diff

Stage:

    git add .

Commit:

    git commit -m "message"

History:

    git log --oneline

File yang tidak boleh masuk repository:

    .env
    vendor/
    node_modules/
    public/build/
    storage/logs/
    backup files

---

## 45. Project Environment

Konfigurasi environment disediakan melalui .env.example; rahasia dan kredensial hanya disimpan pada file .env di masing-masing lingkungan dan tidak boleh masuk repository.

Detail deployment, backup, verifikasi, dan rollback produksi berada pada docs/deployment.md. Jangan menganggap nilai environment lokal sebagai nilai untuk produksi.

## 46. Current Development Priority

Prioritas pengembangan mengikuti docs/roadmap/README.md.

Status saat dokumentasi ini diperbarui:
- Fase 1 — Stabilization & Foundation: audit dan hardening sedang diselesaikan.
- Fase 2–6: belum dimulai sebagai fase pengembangan berikutnya.

Agenda, Inbox, Favorites, dan integrasi import tidak boleh dinyatakan selesai hanya karena route atau view placeholder tersedia.

## 47. Ownership + Assignment Roadmap

Urutan implementasi:

    Authenticated User
            |
            v
        Ownership
            |
            v
        Authorization
            |
            v
       Data Filtering
            |
            v
          Members
            |
            v
        Assignment
            |
            v
         My Tasks

Urutan ini menjaga agar assignment tidak dibangun sebelum sumber Member dan authorization jelas.

---

## 48. Security Principles

TaskFlow mengikuti prinsip:

> Never trust the UI.

Artinya:

- hidden button bukan security;
- hidden menu bukan security;
- frontend validation bukan security;
- authorization harus di backend;
- query harus difilter;
- ownership harus diperiksa;
- assignment harus divalidasi.

---

## 49. UI Principles

TaskFlow menggunakan UI yang sederhana.

Prinsip:

    Simple Navigation
    +
    Clear Context
    +
    Minimal Modal
    +
    Dynamic Data

Contoh:

    Space
      |
      v
    List
      |
      v
    Create Task

Tidak perlu meminta user memilih ulang Workspace, Space, atau List apabila context sudah diketahui sistem.

---

## 50. Architecture Principles

UI dan database mempunyai tanggung jawab berbeda.

    UI
    =
    User Experience

    Database
    =
    Data Integrity

    Backend
    =
    Business Rules

    Authorization
    =
    Security

Workspace tidak terlihat di sidebar bukan alasan untuk menghapus workspace_id.

---

## 51. Current Git Baseline

Repository menggunakan branch:

    main

README menjadi dokumentasi utama project.

Sebelum commit baseline, pastikan:

    git status
    git diff --check

bersih dari masalah formatting dan file yang tidak seharusnya masuk repository.

---

## 52. Current Architecture Summary

    Authenticated User
          |
          v
    Workspace ID 1 (wadah teknis)
          |
          v
    Owned Space / space_members
          |
          +-- Folder (opsional)
          +-- List
                 |
                 v
                Task
                 |
                 +-- Subtask bertingkat
                 +-- Assignee (space_members)
                 +-- Dependency
                 +-- Activity / Note

Direktori members global terpisah dari otorisasi dan assignment Space.

## 53. Golden Rules

1. Jangan hardcode Owner.
2. Workspace ID 1 tetap menjadi wadah teknis internal.
3. Otorisasi data dilakukan berdasarkan Owner/Member Space.
4. space_members adalah sumber keanggotaan Space.
5. members global hanya direktori dan mapping ClickUp.
6. Assignment hanya kepada Owner/Member Space terkait.
7. My Tasks berdasarkan assignee.
8. Space dan List harus dinamis.
9. Authorization harus dilakukan di backend.
10. Hidden UI bukan security.
11. Migration adalah source of truth schema.
12. Subtask tetap menggunakan tabel tasks dan relasi parent_id.
13. Menghapus Task/Subtask melakukan soft-delete pada seluruh keturunan Subtask.
14. Jika Subtask aktif habis, progres Task induk dipertahankan.
15. Favorites tetap pending sampai diminta.
16. Jangan menambah role/permission kompleks sebelum kebutuhan dan desainnya disepakati.

## 54. Development Philosophy

TaskFlow dikembangkan secara bertahap.

    CORE
      |
      +-- Authentication
      +-- Ownership
      +-- Authorization
      +-- Members
      +-- Assignment
      +-- Tasks
      |
      v
    PRODUCTIVITY
      |
      +-- Dashboard
      +-- Agenda
      +-- Inbox
      +-- Board
      +-- Calendar
      +-- Gantt
      |
      v
    INTEGRATION
      |
      +-- ClickUp Import
      |
      v
    ADVANCED
      |
      +-- Collaboration
      +-- Automation
      +-- Reporting

Jangan membangun semua fitur sekaligus.

Setiap layer harus stabil sebelum melanjutkan ke layer berikutnya.

---

## 55. Project Status

TaskFlow masih dalam tahap Active Development.

Fondasi yang telah selesai dan diverifikasi:
- Workspace internal ID 1 dan route manajemen Workspace legacy dinonaktifkan.
- Otorisasi Owner/Member Space dan Task.
- Manajemen keanggotaan Space melalui space_members.
- Pengujian otomatis untuk autentikasi, otorisasi, Task, Subtask, Dependency, route, dan struktur Space.
- GitHub Actions untuk test PHP dan pemeriksaan sintaks JavaScript.

Fase 1 belum dinyatakan selesai sampai audit validasi, integritas relasi, dokumentasi, serta kesiapan rilis ditutup dan CI akhir hijau.

Fitur yang masih menjadi placeholder/backlog antara lain Agenda, Inbox, Favorites, penyimpanan koneksi OAuth jangka panjang, dan import ClickUp/IPAS.

## 56. Final Principle

TaskFlow bukan sekadar clone ClickUp.

ClickUp digunakan sebagai referensi konsep.

TaskFlow mempunyai architecture sendiri:

    Dynamic Data
    +
    Ownership
    +
    Authorization
    +
    Members
    +
    Assignment
    +
    Task Management
    +
    Activity
    +
    Views
    +
    Future ClickUp Import

Prinsip utama:

    Database kuat
    Backend aman
    UI sederhana
    Workflow jelas
    Data dinamis

---

# End

TaskFlow dikembangkan sebagai sistem task management yang modular, aman, dinamis, dan dapat berkembang tanpa mengorbankan struktur database yang sudah ada.

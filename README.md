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

Workspace adalah container tingkat database.

Workspace:

- tetap berada di database;
- memiliki workspace_id;
- menjadi boundary data;
- digunakan untuk filtering;
- dapat digunakan sebagai dasar authorization;
- mendukung kebutuhan integrasi ClickUp;
- tidak ditampilkan sebagai node utama sidebar.

Workspace tidak dihapus dari database hanya karena tidak tampil pada UI.

Owner tidak boleh di-hardcode.

Jangan menggunakan:

    owner_id = 1

sebagai default global.

Owner harus berasal dari user sebenarnya atau business rule yang benar.

---

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

Folder adalah container optional.

    Space
    └── Folder
        ├── List
        └── List

Folder tetap didukung database tetapi tidak menjadi node utama sidebar.

UI saat ini disederhanakan menjadi:

    Space
    └── List

---

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

Subtask tetap menggunakan tabel tasks.

Relasi:

    tasks.parent_id

Contoh:

    Task
    ├── Subtask 1
    ├── Subtask 2
    └── Subtask 3

Tidak dibuat tabel subtasks terpisah.

Aturan:

> Subtask tidak otomatis mengubah due date parent.

---

## 11. Ownership

Ownership merupakan salah satu dasar authorization TaskFlow.

Konsep:

    Owner
      |
      v
    Owned Data
      |
      v
    Authorization
      |
      v
    Filtering

Owner tidak boleh di-hardcode.

Salah:

    $ownerId = 1;

Benar secara konsep:

    $ownerId = auth()->id();

atau owner yang diberikan melalui business rule.

Ownership digunakan untuk menentukan:

- data yang dapat dilihat;
- data yang dapat diubah;
- data yang dapat dihapus;
- filtering Space;
- filtering List;
- filtering Task;
- authorization.

---

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

Query harus mengikuti context user.

    Logged-in User
          |
          v
    Authorized Workspace
          |
          v
    Authorized Space
          |
          v
    Authorized List
          |
          v
    Authorized Task

Hindari query global seperti:

    Task::all();

pada halaman yang seharusnya menggunakan filtering authorization.

---

## 14. Members

Member bukan global People Directory.

User menjadi eligible untuk assignment setelah ditambahkan sebagai Member.

Flow:

    User
      |
      | add / invite
      v
    Member
      |
      | eligible
      v
    Task Assignment

Member berarti boleh dipilih sebagai assignee.

Menjadi Member tidak otomatis mendapatkan Task.

---

## 15. Members Table

Struktur:

    members
    ├── id
    ├── user_id
    ├── invited_by
    ├── clickup_user_id
    ├── created_at
    └── updated_at

Field:

### id

Primary key.

### user_id

User yang menjadi Member.

### invited_by

User yang menambahkan atau mengundang Member.

### clickup_user_id

Mapping user ClickUp untuk kebutuhan integrasi masa depan.

---

## 16. Member Routes

Route utama:

    GET    /members
    POST   /members
    DELETE /members/{member}

Named routes:

    members.index
    members.store
    members.destroy

Halaman menggunakan konsep:

    Anggota

    Kelola anggota yang dapat ditugaskan ke tugas

Saat ini belum ada role atau permission kompleks pada Member.

---

## 17. Assignment

Assignment harus menggunakan Member sebagai sumber assignee.

    Users
      |
      X
      |
    Members
      |
      v
    Assignee Picker
      |
      v
    Task

Tidak boleh mengambil seluruh users sebagai sumber assignee.

Aturan utama:

> Hanya Member yang eligible menjadi assignee.

---

## 18. Task Assignees

Assignment disimpan pada:

    task_assignees

Relasi:

    Task
     |
     +-- Assignee
     |
     +-- Assignee

Database dapat mendukung lebih dari satu assignee jika diperlukan.

Namun sumber assignee tetap:

    members

---

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

Agenda menampilkan pekerjaan berdasarkan waktu.

Contoh:

    Hari Ini
    Besok
    Minggu Ini

Agenda berfokus pada:

- due date;
- pekerjaan mendatang;
- pekerjaan yang perlu diperhatikan.

---

## 27. Inbox

Inbox berisi item yang membutuhkan perhatian user.

Contoh:

- Assignment;
- Mention;
- Comment;
- Task Update;
- Due Reminder;
- Collaboration Activity;
- System Activity.

Inbox bukan daftar seluruh Task.

Konsep:

    Event
      |
      v
    User Attention
      |
      v
    Inbox

---

## 28. Favorites

Favorites saat ini:

    PENDING

dan sementara:

    HIDDEN

Jangan implement atau tampilkan kembali Favorites sampai ada instruksi eksplisit.

---

## 29. Activity

Activity digunakan untuk mencatat aktivitas sistem.

Contoh:

- Task Created;
- Task Updated;
- Task Assigned;
- Status Changed;
- Due Date Changed;
- Member Added;
- Member Removed.

Flow:

    Action
      |
      v
    Event
      |
      v
    Activity

Activity dapat digunakan untuk:

- Activity feed;
- Dashboard;
- Task history;
- Inbox;
- audit trail.

---

## 30. Database Architecture

Database utama:

    MariaDB

Database:

    task_management

Core tables:

    users
    members
    workspaces
    spaces
    folders
    task_lists
    tasks
    task_assignees
    task_dependencies
    activities

Legacy / compatibility:

    workspace_members

---

## 31. Database Relationships

    users
     |
     +-- members
     |
     +-- ownership
     |
     +-- task assignment

    workspaces
     |
     +-- spaces
           |
           +-- folders
           |     |
           |     +-- task_lists
           |
           +-- task_lists
                 |
                 +-- tasks
                        |
                        +-- subtasks
                        +-- task_assignees
                        +-- task_dependencies
                        +-- activities

---

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

    Activity
    Folder
    Member
    Space
    Task
    TaskDependency
    TaskList
    User
    Workspace
    WorkspaceMember

Model harus mengikuti relationship database.

---

## 34. Controllers

Controller utama:

    ActivityController
    AuthController
    Controller
    FolderController
    MemberController
    SettingsController
    SpaceController
    TaskListController
    TaskManagerController
    WorkspaceController
    WorkspaceMemberController

Flow umum:

    Route
      |
      v
    Controller
      |
      v
    Authorization
      |
      v
    Model / Query
      |
      v
    View

---

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

TaskFlow dirancang untuk mendukung one-way import dari ClickUp.

Flow:

    Settings
      |
      └── ClickUp
            |
            v
        Connect ClickUp
            |
            v
           Import
            |
            v
        TaskFlow

Direction:

    ClickUp
       |
       v
    TaskFlow

Bukan synchronization dua arah.

---

## 39. ClickUp Mapping

Field:

    members.clickup_user_id

dipersiapkan untuk mapping user.

Konsep:

    ClickUp User
         |
         v
    clickup_user_id
         |
         v
    TaskFlow Member

Import tidak boleh membuat TaskFlow menjadi hardcoded ClickUp clone.

---

## 40. Import Data

Command import yang tersedia:

    php artisan ipas:import-tasks

Data IPAS v2 hanya merupakan contoh seed.

Aplikasi harus tetap mendukung Workspace, Space, List, User, dan Member lain.

---

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

Install:

    composer install
    npm install

Development:

    php artisan serve
    npm run dev

Migration:

    php artisan migrate
    php artisan migrate:status

Clear cache:

    php artisan optimize:clear

Routes:

    php artisan route:list

Tests:

    php artisan test

---

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

Project:

    /home/debaluk/workspace/task

Local domain:

    https://task.labku.biz.id

Database:

    task_management

PHP:

    8.3.x

MariaDB:

    10.6.x

---

## 46. Current Development Priority

Prioritas utama:

    1. UI cleanup
    2. Ownership
    3. Authorization
    4. Data Filtering
    5. Members
    6. Assignment
    7. My Tasks
    8. Dashboard
    9. Agenda
    10. Inbox

Tahap berikutnya:

    11. Board
    12. Calendar
    13. Gantt
    14. ClickUp Import
    15. Advanced Collaboration
    16. Automation
    17. Reporting

Favorites tetap pending.

---

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

    TASKFLOW
       |
       +-------------------+
       |                   |
       v                   v
    Identity          Authorization
       |                   |
       v                   v
     Users             Ownership
       |
       v
    Members
       |
       v
    Assignment
       |
       v
      Tasks
       |
       +---------+---------+
       |                   |
       v                   v
    Subtasks          Dependencies

Data hierarchy:

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

---

## 53. Golden Rules

1. Jangan hardcode owner.
2. Assignment hanya melalui Member.
3. My Tasks berdasarkan assignee.
4. workspace_id tetap ada.
5. Workspace bukan node UI utama.
6. Space dan List harus dinamis.
7. IPAS v2 hanya data contoh.
8. Authorization harus dilakukan di backend.
9. Hidden UI bukan security.
10. Migration adalah source of truth schema.
11. Subtask tetap menggunakan Task.
12. Subtask tidak mengubah due date parent.
13. Favorites tetap pending sampai diminta.
14. Jangan menambah role/permission kompleks sebelum diperlukan.
15. Context yang sudah diketahui sistem tidak perlu diminta ulang.

---

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

Fokus utama saat ini:

    Ownership
    Authorization
    Data Filtering
    Members
    Assignment
    My Tasks

Pending:

    Favorites
    Advanced Permissions
    Advanced Roles
    ClickUp Import
    Automation
    Advanced Collaboration
    Reporting

---

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

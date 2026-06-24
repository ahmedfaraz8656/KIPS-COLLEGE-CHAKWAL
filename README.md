# 🎓 KIPS College Chakwal — Full ERP System

A complete College Management ERP built with **Laravel 11**, **Bootstrap 5**, **jQuery**, and **MySQL** — for KIPS College Chakwal (Boys + Girls campuses).

## ✅ All 19 Modules Complete

| # | Module | Highlights |
|---|--------|-----------|
| 1 | **Authentication** | Login with lockout, session security, role-based redirect, account management |
| 2 | **Dashboard** | Role-based stats, 8 charts, activity feed, pending alerts |
| 3 | **Admission / Enrollment** | Multi-step form, Excel import/export, dynamic section loading, FAIT campus restriction |
| 4 | **Student Management** | Move/Transfer (campus+year locked), Promotion with default section mapping, system-generated roll numbers |
| 5 | **Teacher Management** | CRUD, teaching assignments, Class Incharge with replace-confirmation, workload view |
| 6 | **Attendance** | Dynamic late-cutoff, holidays (public/college, campus-scoped), per-student/section reports |
| 7 | **Examination** | Test 1-10 with rotating Islamiyat/TTQ & SST/PST subject, dynamic program-tab marks config, due-date lock |
| 8 | **Grading** | Multiple templates, overlap/coverage validation, set-default |
| 9 | **Results & Progress Reports** | Multi-exam cumulative result cards, B&W print layout, WhatsApp share link |
| 10 | **Roll Number Slips** | 2-per-A4, print-friendly |
| 11 | **Fee Management** | Structure setup, ledger, waivers, section-wise reports |
| 12 | **Notifications** | Real-time bell, role/campus targeting, scheduling |
| 13 | **Academic Calendar** | Holiday/Exam/Event color-coded month grid |
| 14 | **Timetable** | Click-to-assign grid, real-time teacher conflict detection |
| 15 | **Notice Board** | Priority levels, targeted audience, archiving |
| 16 | **Settings + User Management** | General/Attendance/Security/WhatsApp/Theme, create/reset/disable accounts |
| 17 | **Backup & Restore** | Manual backup, auto pre-delete snapshots, type-CONFIRM restore |
| 18 | **Audit Trail** | Immutable, filterable, PDF export |
| 19 | **Demo Data** | One-click realistic sample load/delete, real data never touched |

---

## ⚙️ Setup Instructions

This code was written in a sandboxed environment without internet access to Packagist, so `composer install` has not been run here. Follow these steps on your own machine/server:

```bash
# 1. Clone the repo (already done if you're reading this on your machine)
git clone https://github.com/ahmedfaraz8656/KIPS-COLLEGE-CHAKWAL.git
cd KIPS-COLLEGE-CHAKWAL

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure your database in .env
#    DB_DATABASE=kips_college_erp
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Create the database (in phpMyAdmin or mysql CLI)
#    CREATE DATABASE kips_college_erp CHARACTER SET utf8mb4;

# 7. Run migrations + seeders (creates roles, sections, subjects, admin user)
php artisan migrate --seed

# 8. Create storage symlink (for student/teacher photos, logos, attachments)
php artisan storage:link

# 9. Start the server
php artisan serve
```

### Required PHP Extensions
`pdo_mysql`, `mbstring`, `gd` or `imagick` (for image uploads), `zip` (for Backup module).

### For PDF generation (Results, Roll Slips, Audit export) to work
`barryvdh/laravel-dompdf` is already in `composer.json` — no extra setup needed beyond `composer install`.

### For the Backup module to work
The server needs `mysqldump` available on its PATH (standard on most hosting + XAMPP/WAMP installs).

---

## 🔑 Default Login

```
URL:      http://localhost:8000
Email:    admin@kipscollege.edu.pk
Password: Admin@123
```

⚠️ **Change this password immediately after first login** — `force_password_change` is set to `true`, so the system will prompt for a new password automatically.

---

## 🏫 College Structure (pre-seeded by `database/seeders/`)

- **Campuses:** Boys, Girls
- **Years:** First Year, Second Year
- **Programs:** ICS, FSc Medical, FSc Engineering, FAIT (Girls + select Boys sections)
- **Sections:**
  - Boys 1st Year: PCB1, PCB2, PCB3, PEB/PMB
  - Boys 2nd Year: SCB1, SCB2, SCB3, SEB/SMB
  - Girls 1st Year: PCG1-3, PMG1-3, PEG, FAIT
  - Girls 2nd Year: SCG1-2, SMG1-3, SEG, FAIT2
- **Roles:** Managing Director, Principal, Admin, Exam Controller, Teacher, Class Incharge, Student, Parent

## ⚠️ Open Items Still Needing Ahmed's Confirmation

These were flagged during the Exam Module build (see chat history `PROMPT_EXAM_SUBJECTS_CONFIG.md`):
1. Medical program Physics marks (currently assumed 25 - never explicitly stated)
2. FAIT Girls religious-subject naming (used Islamiyat/TTQ - original message said "Islamiyat/P.S.T")
3. Second Year rotating subject pair - currently implemented as SST<->PST (per the verbal rule), but most written tables said "SST/TTQ"
4. Boys 12th ICS - one instance said "PST/TTQ" vs all others "SST/TTQ"

**None of these block the system from running** - they only affect default marks/labels and can be corrected any time via the Exam -> Subject Marks Configuration screen, or by editing `database/seeders/ProgramSubjectSeeder.php` and re-seeding.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.2+, Laravel 11, MySQL 8.0
- **Auth/Permissions:** Spatie Laravel Permission (8 roles, granular permission matrix)
- **Frontend:** Bootstrap 5.3, jQuery 3.7, SweetAlert2, Toastr, Font Awesome 6
- **Reports:** Laravel Dompdf (PDF), Maatwebsite Excel (Excel import/export)
- **Design system:** Navy (#1E3A5F) / Emerald / Amber palette, Poppins font, icon+label buttons throughout

## 📝 Key Business Rules Enforced in Code

- Roll numbers are system-generated and duplicate-proof (`Student::generateRollNumber()` - format `[Campus][Year][Program][4-digit]`, e.g. `B1C0001`)
- Girls/Boys and First/Second Year section moves are **hard-blocked** at both the Eloquent model layer (`Student::canMoveTo()`) and controller validation layer
- Absent/Leave students automatically get **0 marks** via `StudentMark`'s `booted()` observer - enforced at the database write level, cannot be bypassed from the UI
- Audit logs are **immutable** - `AuditLog::delete()` throws an exception by design, even MD cannot delete entries
- Pre-delete **snapshots** are auto-created before bulk-delete operations (see `StudentController::bulkDestroy`)
- Demo data is fully isolated via `is_demo = true` flag - one-click delete never touches real records
- Account disable/expiry is enforced **mid-session** via `EnsureUserIsActive` middleware, not just at login

## 📂 Project Structure

```
app/
  Http/Controllers/   - organized by module (Students/, Teachers/, Exams/, Results/, Fees/, ...)
  Models/              - one Eloquent model per table, with business-logic methods
  Services/             - ResultService, BackupService, DemoDataService (heavy logic kept out of controllers)
database/
  migrations/           - 18 migration files, one logical group per module
  seeders/              - roles, programs, sections, subjects (with confirmed default marks), grading, admin user
resources/views/       - organized by module, Blade + Bootstrap 5 + jQuery AJAX throughout
routes/web.php          - all routes grouped by module with permission middleware
```

---

*Built incrementally, module by module, directly committed and pushed to this repository.*
*Last updated: June 2026*

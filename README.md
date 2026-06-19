# 🎓 KIPS College Chakwal — ERP System

A full College Management ERP built with Laravel 11, Bootstrap 5, jQuery, and MySQL — for KIPS College Chakwal (Boys + Girls campuses).

## ✅ Modules Completed So Far

- [x] **Module 1 — Authentication System** (login, lockout, session security, role redirect)
- [x] **Module 2 — Dashboard** (role-based stats, charts, activity feed, alerts)
- [x] **Module 3 — Admission / Enrollment Module** (multi-step form, Excel import/export, master list, dynamic section loading, FAIT campus restriction)
- [x] **Core Database Structure** (Programs, Sections, Subjects, Students, Teachers, Exams, Grading, Attendance, Audit Logs, Settings)

## 🔜 Coming Next (build order)

4. Student Management — Move/Transfer/Promote
5. Teacher Management
6. Attendance Module
7. Examination Module
8. Grading Module
9. Results & Progress Reports
10. Roll Number Slips
11. Fee Module
12. Notifications
13. Academic Calendar
14. Timetable
15. Notice Board
16. Settings
17. Backup & Restore
18. Audit Trail (view)
19. Demo Data Module

---

## ⚙️ Setup Instructions (run on your own machine/server)

This code was written without running `composer install` (sandboxed environment has no internet access to Packagist). Follow these steps on your local machine or server:

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

# 7. Run migrations + seeders
php artisan migrate --seed

# 8. Create storage symlink (for student/teacher photos)
php artisan storage:link

# 9. Install frontend assets (CDN-based currently, no build step needed yet)

# 10. Start the server
php artisan serve
```

## 🔑 Default Login (created by AdminUserSeeder)

```
URL:      http://localhost:8000
Email:    admin@kipscollege.edu.pk
Password: Admin@123
```

⚠️ **Change this password immediately after first login** — `force_password_change` is set to true so the system will prompt for a new password automatically.

## 🏫 College Structure (pre-seeded)

- **Campuses:** Boys, Girls
- **Years:** First Year, Second Year
- **Programs:** ICS, FSc Medical, FSc Engineering, FAIT (Girls + select Boys sections)
- **Sections:** PCB1-3, PEB/PMB, SCB1-3, SEB/SMB (Boys) | PCG1-3, PMG1-3, PEG, FAIT (Girls 1st Yr) | SCG1-3, SMG1-3, SEG, FAIT2 (Girls 2nd Yr)

## ⚠️ Open Items Needing Confirmation

See `PROMPT_EXAM_SUBJECTS_CONFIG.md` (in project notes) for 4 pending confirmations on:
1. Medical program Physics marks (assumed 25)
2. FAIT Girls religious subject naming
3. Second Year rotation pair: SST↔PST vs SST↔TTQ
4. Boys 12th ICS naming inconsistency

## 🛠️ Tech Stack

- **Backend:** PHP 8.2+, Laravel 11, MySQL 8.0
- **Auth/Permissions:** Spatie Laravel Permission
- **Frontend:** Bootstrap 5.3, jQuery 3.7, DataTables, SweetAlert2, Toastr, Flatpickr
- **Reports:** Laravel Dompdf (PDF), Maatwebsite Excel (Excel import/export)

## 📝 Notes for Continued Development

- All controllers follow the project's strict design system (colors, button styles, SweetAlert2 confirmations) as documented in the Master Prompt.
- Roll numbers are system-generated and duplicate-proof (`Student::generateRollNumber()`).
- Girls/Boys and First/Second Year section moves are restricted at both the Eloquent model layer (`Student::canMoveTo()`) and Form Request validation layer.
- Absent/Leave students automatically get 0 marks via the `StudentMark` model's `booted()` observer — this is enforced at the database write level, cannot be bypassed.
- Audit logs are immutable — `AuditLog::delete()` throws an exception by design.

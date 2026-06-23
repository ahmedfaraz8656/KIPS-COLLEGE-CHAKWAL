<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Students\StudentController;
use App\Http\Controllers\Students\StudentImportExportController;
use App\Http\Controllers\Students\StudentTransferController;
use App\Http\Controllers\Teachers\TeacherController;
use App\Http\Controllers\Teachers\TeacherAssignmentController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\HolidayController;
use App\Http\Controllers\Exams\ExamController;
use App\Http\Controllers\Exams\MarksEntryController;
use App\Http\Controllers\Exams\GradingController;
use App\Http\Controllers\Results\ResultController;
use App\Http\Controllers\Results\RollSlipController;
use App\Http\Controllers\Fees\FeeController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Timetable\TimetableController;
use App\Http\Controllers\Notices\NoticeController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Settings\UserManagementController;
use Illuminate\Support\Facades\Route;

// ─── GUEST ROUTES ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/',      [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login',[LoginController::class, 'login'])->name('login.post');
});

// ─── AUTHENTICATED ROUTES ────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (role-based)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard AJAX stats endpoints
    Route::prefix('dashboard/api')->group(function () {
        Route::get('/stats',          [DashboardController::class, 'getStats']);
        Route::get('/chart/gender',   [DashboardController::class, 'genderChart']);
        Route::get('/chart/sections', [DashboardController::class, 'sectionsChart']);
        Route::get('/chart/programs', [DashboardController::class, 'programsChart']);
        Route::get('/chart/attendance',[DashboardController::class,'attendanceChart']);
        Route::get('/activity',       [DashboardController::class, 'recentActivity']);
        Route::get('/alerts',         [DashboardController::class, 'pendingAlerts']);
    });

    // ── STUDENTS / ADMISSION MODULE ──────────────────────────────
    Route::middleware('permission:manage students')->prefix('students')->name('students.')->group(function () {
        Route::get('/',                [StudentController::class, 'index'])->name('index');
        Route::get('/list',            [StudentController::class, 'list'])->name('list'); // AJAX DataTable
        Route::get('/create',          [StudentController::class, 'create'])->name('create');
        Route::post('/',               [StudentController::class, 'store'])->name('store');
        Route::get('/sections-for',    [StudentController::class, 'sectionsFor'])->name('sections-for'); // AJAX
        Route::get('/{student}',       [StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit',  [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}',       [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}',    [StudentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete',    [StudentController::class, 'bulkDestroy'])->name('bulk-delete');

        // Import / Export
        Route::get('/import/template',  [StudentImportExportController::class, 'downloadTemplate'])->name('import.template');
        Route::post('/import/preview',  [StudentImportExportController::class, 'preview'])->name('import.preview');
        Route::post('/export',          [StudentImportExportController::class, 'export'])->name('export');

        // ── MODULE 4: Move / Transfer ───────────────────────────
        Route::get('/transfer',                [StudentTransferController::class, 'index'])->name('transfer');
        Route::get('/transfer/sections',        [StudentTransferController::class, 'sectionsByFilter'])->name('transfer.sections');
        Route::get('/transfer/students',        [StudentTransferController::class, 'studentsInSection'])->name('transfer.students');
        Route::post('/transfer/move',           [StudentTransferController::class, 'move'])->name('transfer.move');

        // ── MODULE 4: Promotion (1st Year → 2nd Year) ───────────
        Route::get('/promote',   [StudentTransferController::class, 'promotionIndex'])->name('promote');
        Route::post('/promote',  [StudentTransferController::class, 'promote'])->name('promote.execute');
    });

    // ── MODULE 5: TEACHER MANAGEMENT ────────────────────────────
    Route::middleware('permission:manage teachers')->prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/',               [TeacherController::class, 'index'])->name('index');
        Route::get('/list',           [TeacherController::class, 'list'])->name('list');
        Route::get('/create',         [TeacherController::class, 'create'])->name('create');
        Route::post('/',              [TeacherController::class, 'store'])->name('store');
        Route::get('/{teacher}',      [TeacherController::class, 'show'])->name('show');
        Route::get('/{teacher}/edit', [TeacherController::class, 'edit'])->name('edit');
        Route::put('/{teacher}',      [TeacherController::class, 'update'])->name('update');
        Route::delete('/{teacher}',   [TeacherController::class, 'destroy'])->name('destroy');
        Route::post('/{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{teacher}/workload', [TeacherController::class, 'workload'])->name('workload');

        // Teaching Assignments
        Route::get('/sections/subjects',               [TeacherAssignmentController::class, 'subjectsForSection'])->name('sections.subjects');
        Route::post('/{teacher}/assignments',          [TeacherAssignmentController::class, 'addAssignment'])->name('assignments.add');
        Route::delete('/assignments/{assignment}',     [TeacherAssignmentController::class, 'removeAssignment'])->name('assignments.remove');

        // Class Incharge
        Route::get('/incharge/check-conflict',         [TeacherAssignmentController::class, 'checkInchargeConflict'])->name('incharge.check');
        Route::post('/{teacher}/incharge',              [TeacherAssignmentController::class, 'assignIncharge'])->name('incharge.assign');
        Route::delete('/incharge/{incharge}',           [TeacherAssignmentController::class, 'removeIncharge'])->name('incharge.remove');
    });

    // ── MODULE 6: ATTENDANCE ────────────────────────────────────
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::middleware('permission:mark attendance')->group(function () {
            Route::get('/mark',          [AttendanceController::class, 'markPage'])->name('mark');
            Route::get('/mark/students', [AttendanceController::class, 'loadStudents'])->name('mark.students');
            Route::post('/mark/save',    [AttendanceController::class, 'save'])->name('mark.save');
            Route::post('/mark/all',     [AttendanceController::class, 'markAll'])->name('mark.all');
        });

        Route::middleware('permission:view attendance')->group(function () {
            Route::get('/reports',          [AttendanceController::class, 'reportsPage'])->name('reports');
            Route::get('/reports/section',  [AttendanceController::class, 'sectionReport'])->name('reports.section');
        });

        // Student/Parent self-service: own-record check is enforced inside the controller
        Route::middleware('permission:view attendance|view own attendance|view child attendance')->group(function () {
            Route::get('/reports/student',  [AttendanceController::class, 'studentReport'])->name('reports.student');
        });

        Route::middleware('permission:manage holidays')->group(function () {
            Route::get('/holidays',         [HolidayController::class, 'index'])->name('holidays');
            Route::post('/holidays',        [HolidayController::class, 'store'])->name('holidays.store');
            Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        });
    });

    // ── MODULE 7: EXAMINATION ───────────────────────────────────
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::middleware('permission:create exam')->group(function () {
            Route::get('/',                  [ExamController::class, 'index'])->name('index');
            Route::get('/create',            [ExamController::class, 'create'])->name('create');
            Route::get('/default-subjects',  [ExamController::class, 'getDefaultSubjects'])->name('default-subjects');
            Route::get('/affected-sections',  [ExamController::class, 'affectedSections'])->name('affected-sections');
            Route::post('/',                 [ExamController::class, 'store'])->name('store');
            Route::get('/{exam}',            [ExamController::class, 'show'])->name('show');
            Route::delete('/{exam}',         [ExamController::class, 'destroy'])->name('destroy');
            Route::post('/{exam}/extend-due-date', [ExamController::class, 'extendDueDate'])->name('extend-due-date');
            Route::get('/{exam}/incomplete-teachers', [ExamController::class, 'incompleteTeachers'])->name('incomplete-teachers');
        });

        // Marks Entry — accessible to Teachers too (not just exam-creators)
        Route::middleware('permission:enter marks')->prefix('marks-entry')->name('marks-entry.')->group(function () {
            Route::get('/',              [MarksEntryController::class, 'index'])->name('index');
            Route::get('/sections',      [MarksEntryController::class, 'sectionsForEntry'])->name('sections');
            Route::get('/subjects',      [MarksEntryController::class, 'subjectsForEntry'])->name('subjects');
            Route::get('/table',         [MarksEntryController::class, 'loadMarksTable'])->name('table');
            Route::post('/save',         [MarksEntryController::class, 'saveMarks'])->name('save');
            Route::post('/set-same',     [MarksEntryController::class, 'setSameMark'])->name('set-same');
        });

        // ── MODULE 8: Grading Templates ─────────────────────────
        Route::middleware('permission:manage grading')->prefix('grading')->name('grading.')->group(function () {
            Route::get('/',             [GradingController::class, 'index'])->name('index');
            Route::post('/',            [GradingController::class, 'store'])->name('store');
            Route::put('/{template}',   [GradingController::class, 'update'])->name('update');
            Route::post('/{template}/set-default', [GradingController::class, 'setDefault'])->name('set-default');
            Route::delete('/{template}', [GradingController::class, 'destroy'])->name('destroy');
        });

        // ── MODULE 9: Results & Progress Reports ────────────────
        Route::middleware('permission:view results')->prefix('results')->name('results.')->group(function () {
            Route::get('/',                  [ResultController::class, 'index'])->name('index');
            Route::get('/resolve-students',  [ResultController::class, 'resolveStudents'])->name('resolve-students');
            Route::post('/preview',          [ResultController::class, 'preview'])->name('preview');
            Route::post('/pdf',              [ResultController::class, 'generatePdf'])->name('pdf');
            Route::post('/share-link',       [ResultController::class, 'shareLink'])->name('share-link');
        });

        // ── MODULE 10: Roll Number Slips ─────────────────────────
        Route::middleware('permission:create exam')->prefix('roll-slips')->name('roll-slips.')->group(function () {
            Route::get('/',     [RollSlipController::class, 'index'])->name('index');
            Route::post('/pdf', [RollSlipController::class, 'generatePdf'])->name('pdf');
        });
    });

    // ── MODULE 11: FEE MANAGEMENT ───────────────────────────────
    Route::middleware('permission:manage fees')->prefix('fees')->name('fees.')->group(function () {
        Route::get('/structure',          [FeeController::class, 'structureIndex'])->name('structure');
        Route::post('/structure',         [FeeController::class, 'storeStructure'])->name('structure.store');
        Route::delete('/structure/{structure}', [FeeController::class, 'destroyStructure'])->name('structure.destroy');

        Route::get('/ledger/{student}',   [FeeController::class, 'ledger'])->name('ledger');
        Route::post('/ledger/{student}',  [FeeController::class, 'storePayment'])->name('ledger.store');
        Route::post('/waiver/{fee}',      [FeeController::class, 'applyWaiver'])->name('waiver');
        Route::delete('/payment/{fee}',   [FeeController::class, 'destroyPayment'])->name('payment.destroy');

        Route::get('/reports', [FeeController::class, 'reports'])->name('reports');
    });

    // ── MODULE 12: NOTIFICATIONS ────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Bell dropdown — every logged-in user can read their own
        Route::get('/bell',              [NotificationController::class, 'bellList'])->name('bell');
        Route::post('/{recipient}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all',         [NotificationController::class, 'markAllRead'])->name('read-all');

        // Admin-only: create + history
        Route::middleware('permission:manage notices')->group(function () {
            Route::get('/',  [NotificationController::class, 'index'])->name('index');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
        });
    });

    // ── MODULE 13: ACADEMIC CALENDAR ────────────────────────────
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/',              [CalendarController::class, 'index'])->name('index');
        Route::get('/events',        [CalendarController::class, 'eventsForMonth'])->name('events');

        Route::middleware('permission:manage calendar')->group(function () {
            Route::post('/event',              [CalendarController::class, 'storeEvent'])->name('event.store');
            Route::delete('/holiday/{holiday}', [CalendarController::class, 'destroyHoliday'])->name('holiday.destroy');
            Route::delete('/event/{event}',     [CalendarController::class, 'destroyEvent'])->name('event.destroy');
        });
    });

    // ── MODULE 14: TIMETABLE ────────────────────────────────────
    Route::prefix('timetable')->name('timetable.')->group(function () {
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::get('/section/{section}',          [TimetableController::class, 'sectionGrid'])->name('section.grid');
        Route::get('/section/{section}/subjects', [TimetableController::class, 'subjectsForSection'])->name('section.subjects');
        Route::get('/teacher/{teacher}',          [TimetableController::class, 'teacherView'])->name('teacher.view');

        Route::middleware('permission:manage timetable')->group(function () {
            Route::post('/check-conflict', [TimetableController::class, 'checkConflict'])->name('check-conflict');
            Route::post('/assign',         [TimetableController::class, 'assign'])->name('assign');
            Route::delete('/entry/{entry}', [TimetableController::class, 'removeEntry'])->name('entry.remove');
        });
    });

    // ── MODULE 15: NOTICE BOARD ─────────────────────────────────
    Route::prefix('notices')->name('notices.')->group(function () {
        Route::get('/',              [NoticeController::class, 'index'])->name('index');
        Route::post('/{notice}/read',[NoticeController::class, 'markRead'])->name('read');

        Route::middleware('permission:manage notices')->group(function () {
            Route::post('/',                 [NoticeController::class, 'store'])->name('store');
            Route::post('/{notice}/archive',  [NoticeController::class, 'archive'])->name('archive');
            Route::delete('/{notice}',        [NoticeController::class, 'destroy'])->name('destroy');
        });
    });

    // ── MODULE 16: SETTINGS (MD/Admin only) ─────────────────────
    Route::middleware('permission:manage settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/',                    [SettingsController::class, 'index'])->name('index');
        Route::post('/general',            [SettingsController::class, 'updateGeneral'])->name('general');
        Route::post('/attendance',         [SettingsController::class, 'updateAttendance'])->name('attendance');
        Route::post('/security',           [SettingsController::class, 'updateSecurity'])->name('security');
        Route::post('/notifications',      [SettingsController::class, 'updateNotificationSettings'])->name('notifications');
        Route::post('/theme',              [SettingsController::class, 'updateTheme'])->name('theme');
        Route::post('/force-logout/{user}',[SettingsController::class, 'forceLogout'])->name('force-logout');

        // User Management
        Route::get('/users',               [UserManagementController::class, 'page'])->name('users.page');
        Route::get('/users/list',          [UserManagementController::class, 'index'])->name('users.list');
        Route::post('/users',              [UserManagementController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-status',  [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/roles',          [UserManagementController::class, 'updateRoles'])->name('users.roles');
        Route::post('/users/{user}/access-expiry',  [UserManagementController::class, 'updateAccessExpiry'])->name('users.access-expiry');
        Route::get('/users/{user}/login-history',   [UserManagementController::class, 'loginHistory'])->name('users.login-history');
        Route::delete('/users/{user}',              [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});

// Public signed route (24h expiring share link) — outside auth, validated by signature
Route::get('/results/shared', [ResultController::class, 'sharedPdf'])
    ->name('results.shared-pdf')->middleware('signed');

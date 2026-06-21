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
    });
});

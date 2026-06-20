<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Students\StudentController;
use App\Http\Controllers\Students\StudentImportExportController;
use App\Http\Controllers\Students\StudentTransferController;
use App\Http\Controllers\Teachers\TeacherController;
use App\Http\Controllers\Teachers\TeacherAssignmentController;
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
});

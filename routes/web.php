<?php
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated Institutional Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/account/password', [AuthController::class, 'updatePassword'])->name('account.password');

    // Executive & Operational Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Document Registration, Search & Lifecycle
    Route::get('/documents/check-duplicate', [DocumentController::class, 'checkDuplicate'])->name('documents.check-duplicate');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::post('/documents/{document}/retry-ocr', [DocumentController::class, 'retryOcr'])->name('documents.retry-ocr');
    Route::post('/documents/{document}/assign', [DocumentController::class, 'assign'])->name('documents.assign');

    // Private Document File Streaming & Multi-File Attachments
    Route::get('/documents/{document}/files/{file}/preview', [DocumentFileController::class, 'preview'])->name('documents.files.preview');
    Route::get('/documents/{document}/files/{file}/download', [DocumentFileController::class, 'download'])->name('documents.files.download');
    Route::post('/documents/{document}/files', [DocumentFileController::class, 'storeAdditional'])->name('documents.files.store');

    // Digital Archive Vault & Restoration
    Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
    Route::post('/documents/{document}/archive', [ArchiveController::class, 'store'])->name('documents.archive');
    Route::post('/documents/{document}/restore', [ArchiveController::class, 'restore'])->name('documents.restore');

    // Categories & Offices Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/offices', [OfficeController::class, 'index'])->name('offices.index');
    Route::post('/offices', [OfficeController::class, 'storeOffice'])->name('offices.store');
    Route::post('/departments', [OfficeController::class, 'storeDepartment'])->name('departments.store');

    // Accountability & Immutable Audit Trail
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Reports, Exports & Encrypted Backups
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/print', [ReportController::class, 'exportPrintPdf'])->name('reports.export.print');
    Route::post('/reports/backup', [ReportController::class, 'runBackup'])->name('reports.backup');

    // Staff & Role Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
});

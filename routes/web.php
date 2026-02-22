<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Applicant;
use App\Http\Controllers\Approver;
use App\Http\Controllers\Public as PublicCtrl;
use App\Http\Controllers\Pic;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

/* ── PUBLIC (no auth) ────────────────────────────────── */
Route::get('proposals/search', [PublicCtrl\ProposalSearchController::class, 'index'])
     ->name('proposals.search');
Route::get('proposals/search/{code}', [PublicCtrl\ProposalSearchController::class, 'show'])
     ->name('proposals.search.show');
Route::get('proposals/{proposal}/certificate', [PublicCtrl\ProposalSearchController::class, 'downloadCertificate'])
     ->name('proposals.certificate.download');

/* ── AUTHENTICATED ───────────────────────────────────── */
Route::middleware('auth')->group(function () {

    /* Dashboard */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn () => redirect()->route('dashboard'));

    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /* ── SHARED ROUTES (authorization via Gate/Policy, no role middleware) ─ */

    // Disbursement PDF report — Superadmin, Auditor, owning PIC
    Route::get('disbursements/{disbursement}/report',
        [Pic\ReportController::class, 'picReport']
    )->name('disbursements.report');

    // Proof ZIP download (signed URL)
    Route::get('disbursements/{disbursement}/proofs-zip',
        [Pic\ReportController::class, 'downloadProofsZip']
    )->name('disbursements.proofs.zip');

    // Proof file download (signed URL, used by QR codes)
    Route::get('transactions/{transaction}/proof',
        [Pic\TransactionController::class, 'downloadProof']
    )->name('transactions.proof.download');

    /* ── SUPERADMIN ───────────────────────────────────── */
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {

        // Budget Allocations
        Route::resource('budget-allocations', Admin\BudgetAllocationController::class);

        // Disbursements (with proposal fetch helper)
        Route::resource('disbursements', Admin\DisbursementController::class);
        Route::get('approved-proposals', [Admin\DisbursementController::class, 'approvedProposals'])
             ->name('disbursements.approved-proposals');

        // Users
        Route::resource('users', Admin\UserController::class);

        // Reports
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/global/pdf', [Admin\ReportController::class, 'globalPdf'])->name('reports.global');

        // Proposals
        Route::get('proposals', [Admin\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('proposals/{proposal}', [Admin\ProposalController::class, 'show'])->name('proposals.show');
        Route::post('proposals/{proposal}/forward', [Admin\ProposalController::class, 'forward'])->name('proposals.forward');
        Route::post('proposals/{proposal}/reject', [Admin\ProposalController::class, 'reject'])->name('proposals.reject');
        Route::get('proposals/{proposal}/pdf', [Admin\ProposalController::class, 'downloadProposalPdf'])->name('proposals.pdf');
        Route::get('proposals/{proposal}/certificate', [Admin\ProposalController::class, 'downloadCertificate'])->name('proposals.certificate');

        // Settings
        Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });

    /* ── PIC ──────────────────────────────────────────── */
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {

        Route::get('disbursements', [Pic\DisbursementController::class, 'index'])
             ->name('disbursements.index');
        Route::get('disbursements/{disbursement}', [Pic\DisbursementController::class, 'show'])
             ->name('disbursements.show');

        Route::get('disbursements/{disbursement}/transactions/create',
            [Pic\TransactionController::class, 'create']
        )->middleware('activity.active')->name('disbursements.transactions.create');

        Route::post('disbursements/{disbursement}/transactions',
            [Pic\TransactionController::class, 'store']
        )->middleware('activity.active')->name('disbursements.transactions.store');

        Route::get('disbursements/{disbursement}/transactions/{transaction}/edit',
            [Pic\TransactionController::class, 'edit']
        )->middleware('activity.active')->name('disbursements.transactions.edit');

        Route::put('disbursements/{disbursement}/transactions/{transaction}',
            [Pic\TransactionController::class, 'update']
        )->middleware('activity.active')->name('disbursements.transactions.update');

        Route::delete('disbursements/{disbursement}/transactions/{transaction}',
            [Pic\TransactionController::class, 'destroy']
        )->middleware('activity.active')->name('disbursements.transactions.destroy');
    });

    /* ── AUDITOR ──────────────────────────────────────── */
    Route::middleware('role:auditor')->prefix('auditor')->name('auditor.')->group(function () {
        Route::get('budget-allocations', [Admin\BudgetAllocationController::class, 'index'])
             ->name('budget-allocations.index');
        Route::get('budget-allocations/{budgetAllocation}', [Admin\BudgetAllocationController::class, 'show'])
             ->name('budget-allocations.show');
        Route::get('disbursements', [Admin\DisbursementController::class, 'index'])
             ->name('disbursements.index');
        Route::get('disbursements/{disbursement}', [Admin\DisbursementController::class, 'show'])
             ->name('disbursements.show');
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    });

    /* ── APPLICANT ────────────────────────────────────── */
    Route::middleware('role:applicant')->prefix('applicant')->name('applicant.')->group(function () {
        Route::get('proposals', [Applicant\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('proposals/create', [Applicant\ProposalController::class, 'create'])->name('proposals.create');
        Route::post('proposals', [Applicant\ProposalController::class, 'store'])->name('proposals.store');
        Route::get('proposals/{proposal}', [Applicant\ProposalController::class, 'show'])->name('proposals.show');
        Route::get('proposals/{proposal}/certificate', [Applicant\ProposalController::class, 'downloadCertificate'])
             ->name('proposals.certificate');
    });

    /* ── APPROVER ─────────────────────────────────────── */
    Route::middleware('role:approver')->prefix('approver')->name('approver.')->group(function () {
        Route::get('proposals', [Approver\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('proposals/{proposal}', [Approver\ProposalController::class, 'show'])->name('proposals.show');
        Route::post('proposals/{proposal}/approve', [Approver\ProposalController::class, 'approve'])->name('proposals.approve');
        Route::get('proposals/{proposal}/pdf', [Approver\ProposalController::class, 'downloadProposalPdf'])->name('proposals.pdf');
    });
});

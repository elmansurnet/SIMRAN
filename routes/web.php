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

/* ── PUBLIC ──────────────────────────────────────────── */
Route::get('proposals/search', [PublicCtrl\ProposalSearchController::class, 'index'])
     ->name('proposals.search');
Route::get('proposals/search/{code}', [PublicCtrl\ProposalSearchController::class, 'show'])
     ->name('proposals.search.show');
Route::get('proposals/{proposal}/certificate', [PublicCtrl\ProposalSearchController::class, 'downloadCertificate'])
     ->name('proposals.certificate.download');

Route::get('shared/transactions/{transaction}/proof', [Pic\TransactionController::class, 'downloadProof'])
     ->name('transactions.proof.download')->middleware('signed');
Route::get('shared/disbursements/{disbursement}/proofs-zip', [Pic\ReportController::class, 'downloadProofsZip'])
     ->name('disbursements.proofs.zip')->middleware('signed');

/* ── AUTHENTICATED ───────────────────────────────────── */
Route::middleware('auth')->group(function () {

    /* ── Dashboard & Profile ────────────────────────────────── */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', fn () => redirect()->route('dashboard'));
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        /* ── SHARED DOWNLOAD ROUTES ─────────────────────────────────
        |
        | These two routes MUST live outside any role-specific middleware
        | so that Superadmin, Auditor, and PIC can all reach them.
        |
        | Authorization is enforced by the policy (TransactionPolicy and
        | DisbursementPolicy), not by route-level role checks.
        |
        | Route names MUST match what ReportService::generatePicPdf() encodes
        | in the QR codes:
        |   'transactions.proof.download'   (no pic. prefix)
        |   'disbursements.proofs.zip'      (no pic. prefix)
        |
        ─────────────────────────────────────────────────────────── */

        Route::get('transactions/{transaction}/proof', [Pic\TransactionController::class, 'downloadProof'])
        ->name('transactions.proof.auth');

        // // Individual proof file — serves inline; opened via click or QR scan
        // Route::get(
        //     'shared/transactions/{transaction}/proof',
        //     [Pic\TransactionController::class, 'downloadProof']
        // )->name('transactions.proof.download');

        // // ZIP of all proofs — requires valid signed URL from QR code
        // Route::get(
        //     'shared/disbursements/{disbursement}/proofs-zip',
        //     [Pic\ReportController::class, 'downloadProofsZip']
        // )->name('disbursements.proofs.zip');

    /* ── SUPERADMIN ───────────────────────────────────── */
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {

        Route::resource('budget-allocations', Admin\BudgetAllocationController::class);
        Route::resource('disbursements', Admin\DisbursementController::class);
        Route::get('approved-proposals', [Admin\DisbursementController::class, 'approvedProposals'])
             ->name('disbursements.approved-proposals');
        Route::resource('users', Admin\UserController::class);

        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/global/pdf', [Admin\ReportController::class, 'globalPdf'])->name('reports.global');

        Route::get('proposals', [Admin\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('proposals/{proposal}', [Admin\ProposalController::class, 'show'])->name('proposals.show');
        Route::post('proposals/{proposal}/forward', [Admin\ProposalController::class, 'forward'])->name('proposals.forward');
        Route::post('proposals/{proposal}/reject', [Admin\ProposalController::class, 'reject'])->name('proposals.reject');
        Route::get('proposals/{proposal}/pdf', [Admin\ProposalController::class, 'downloadProposalPdf'])->name('proposals.pdf');
        Route::get('proposals/{proposal}/certificate', [Admin\ProposalController::class, 'downloadCertificate'])->name('proposals.certificate');

        Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });

    /* ── PIC ─────────────────────────────────────────────────── */
    Route::middleware('role:pic')->prefix('pic')->name('pic.')->group(function () {

        // Disbursement list & detail
        Route::get('disbursements', [Pic\DisbursementController::class, 'index'])
             ->name('disbursements.index');
        Route::get('disbursements/{disbursement}', [Pic\DisbursementController::class, 'show'])
             ->name('disbursements.show');

        // PDF report (generated on-the-fly; no signed URL needed)
        Route::get('disbursements/{disbursement}/report', [Pic\ReportController::class, 'picReport'])
             ->name('disbursements.report');

        // ── Transaction CRUD — all gated by EnsureActivityActive ──

        // Batch delete MUST be declared before the {transaction} wildcard
        Route::delete(
            'disbursements/{disbursement}/transactions',
            [Pic\TransactionController::class, 'batchDestroy']
        )->middleware('activity.active')->name('disbursements.transactions.batch-destroy');

        Route::get(
            'disbursements/{disbursement}/transactions/create',
            [Pic\TransactionController::class, 'create']
        )->middleware('activity.active')->name('disbursements.transactions.create');

        Route::post(
            'disbursements/{disbursement}/transactions',
            [Pic\TransactionController::class, 'store']
        )->middleware('activity.active')->name('disbursements.transactions.store');

        Route::get(
            'disbursements/{disbursement}/transactions/{transaction}/edit',
            [Pic\TransactionController::class, 'edit']
        )->middleware('activity.active')->name('disbursements.transactions.edit');

        Route::put(
            'disbursements/{disbursement}/transactions/{transaction}',
            [Pic\TransactionController::class, 'update']
        )->middleware('activity.active')->name('disbursements.transactions.update');

        Route::delete(
            'disbursements/{disbursement}/transactions/{transaction}',
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
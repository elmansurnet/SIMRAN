<?php

namespace App\Services;

use App\Models\BudgetAllocation;
use App\Models\Disbursement;
use App\Models\Proposal;
use App\Models\Transaction;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;

class ReportService
{
    /**
     * F4 / Folio paper size in dompdf points (1 inch = 72 pt).
     *   width : 8.27 in × 72 = 595.44 pt
     *   height: 12.99 in × 72 = 935.28 pt
     *
     * dompdf's setPaper() accepts a custom size as [x0, y0, x1, y1].
     * Defined once here so all three report methods share the same value.
     *
     * CHANGE: added this constant. Previously each method passed a string
     * ('A4') to setPaper(); now they all reference self::F4 instead.
     */
    private const F4 = [0, 0, 595.44, 935.28];

    /**
     * Generate QR code PNG via Imagick extension.
     * Returns base64-encoded PNG string for embedding in PDF.
     *
     * Requires: ext-imagick (php_imagick-3.8.1-8.2-nts-vs16-x86_64.dll)
     *
     * @param  string  $content  URL or text to encode
     * @param  int     $size     Pixel dimensions (use 120+ for print quality)
     */
    private function makeQr(string $content, int $size = 120): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),  // size, margin-in-modules
            new ImagickImageBackEnd()     // ← Imagick instead of GD
        );

        return base64_encode((new Writer($renderer))->writeString($content));
    }

    // ──────────────────────────────────────────────────────────
    //  PIC / Disbursement Report PDF
    // ──────────────────────────────────────────────────────────
    public function generatePicPdf(Disbursement $disbursement): string
    {
        $disbursement->load([
            'transactions' => fn ($q) => $q->orderBy('transaction_date')->orderBy('created_at'),
            'pic',
            'budgetAllocation',
        ]);

        $transactions = $disbursement->transactions;

        // Per-transaction QR codes (120px — crisp at PDF print size)
        $qrCodes = [];
        foreach ($transactions as $tx) {
            if ($tx->hasProof()) {
                $url = URL::temporarySignedRoute(
                    'transactions.proof.download',
                    now()->addDays(7),
                    ['transaction' => $tx->id]
                );
                $qrCodes[$tx->id] = $this->makeQr($url, 120);
            }
        }

        // ZIP QR code (footer, slightly larger)
        $zipUrl = URL::temporarySignedRoute(
            'disbursements.proofs.zip',
            now()->addDays(7),
            ['disbursement' => $disbursement->id]
        );
        $zipQr = $this->makeQr($zipUrl, 140);

        $pdf = Pdf::loadView(
            'pdf.pic-report',
            compact('disbursement', 'transactions', 'qrCodes', 'zipQr')
        )->setPaper(self::F4, 'portrait'); // CHANGE: 'A4' → self::F4

        return $pdf->output();
    }

    // ──────────────────────────────────────────────────────────
    //  Global Report PDF
    // ──────────────────────────────────────────────────────────
    public function generateGlobalPdf(): string
    {
        $totalBudget     = (float) BudgetAllocation::sum('amount');
        $totalDisbursed  = (float) Disbursement::sum('amount');
        $totalExpense    = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome     = (float) Transaction::where('type', 'income')->sum('amount');
        $remainingBudget = $totalBudget - $totalDisbursed;
        $currentCash     = $totalDisbursed + $totalIncome - $totalExpense;

        $disbursements = Disbursement::with(['pic', 'budgetAllocation'])
            ->orderByDesc('created_at')->get();

        $allocations = BudgetAllocation::whereNull('deleted_at')
            ->with('disbursements')->get();

        $pdf = Pdf::loadView('pdf.global-report', compact(
            'allocations', 'disbursements',
            'totalBudget', 'totalDisbursed', 'totalExpense',
            'totalIncome', 'remainingBudget', 'currentCash'
        ))->setPaper(self::F4, 'portrait'); // CHANGE: 'A4', 'landscape' → self::F4, 'portrait'
                                            // The blade view has been redesigned for portrait layout.

        return $pdf->output();
    }

    // ──────────────────────────────────────────────────────────
    //  Approval Certificate PDF
    // ──────────────────────────────────────────────────────────
    public function generateApprovalCertificate(Proposal $proposal): string
    {
        $proposal->load(['applicant', 'pic', 'approvers.user', 'reviewer']);

        // QR links to the public proposal search/verify page
        $searchUrl = route('proposals.search.show', $proposal->code);
        $qrCode    = $this->makeQr($searchUrl, 130);

        $pdf = Pdf::loadView(
            'pdf.approval-certificate',
            compact('proposal', 'qrCode')
        )->setPaper(self::F4, 'portrait'); // CHANGE: 'A4' → self::F4

        return $pdf->output();
    }

    // ──────────────────────────────────────────────────────────
    //  Build proof ZIP on demand (called by signed download route)
    // ──────────────────────────────────────────────────────────
    public function buildProofZip(Disbursement $disbursement): string
    {
        $transactions = $disbursement->transactions()
            ->whereNotNull('proof_path')
            ->get();

        $tmpDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR
            . 'proofs_' . $disbursement->id . '_' . time() . '_' . uniqid() . '.zip';

        $zip    = new ZipArchive();
        $result = $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \RuntimeException("Tidak bisa membuat file ZIP. Kode error: {$result}");
        }

        $added = 0;
        foreach ($transactions as $tx) {
            if (Storage::exists($tx->proof_path)) {
                $content  = Storage::get($tx->proof_path);
                $filename = $tx->id . '_' . ($tx->proof_original_name ?? basename($tx->proof_path));
                $zip->addFromString($filename, $content);
                $added++;
            }
        }

        if ($added === 0) {
            $zip->addFromString(
                'TIDAK_ADA_BUKTI.txt',
                'Tidak ada file bukti yang ditemukan untuk pencairan: ' . $disbursement->name
            );
        }

        $zip->close();

        if (! file_exists($tmpPath)) {
            throw new \RuntimeException("File ZIP tidak berhasil dibuat: {$tmpPath}");
        }

        return $tmpPath;
    }
}
<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    /**
     * Generate a PDF report for a disbursement.
     * Accessible by the owning PIC, Superadmin, and Auditor.
     */
    public function picReport(Request $request, Disbursement $disbursement): Response
    {
        // Manual authorization — works for any role that passes the policy
        if (Gate::denies('printReport', $disbursement)) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak laporan ini.');
        }

        $pdf = $this->service->generatePicPdf($disbursement);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-' . Str::slug($disbursement->name) . '-' . now()->format('Ymd') . '.pdf"',
        ]);
    }

    /**
     * Download a ZIP of all proof files for a disbursement.
     * Accessible via signed URL by the owning PIC or Superadmin.
     */
    public function downloadProofsZip(Request $request, Disbursement $disbursement): BinaryFileResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'Link tidak valid atau sudah kedaluwarsa.');

        if (Gate::denies('printReport', $disbursement)) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh bukti ini.');
        }

        $zipPath = $this->service->buildProofZip($disbursement);

        return response()->download(
            $zipPath,
            'bukti-' . Str::slug($disbursement->name) . '-' . now()->format('Ymd') . '.zip'
        )->deleteFileAfterSend(true);
    }
}
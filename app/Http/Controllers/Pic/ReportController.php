<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    /**
     * Generate and stream the PIC PDF report.
     *
     * $this->authorize() works because the base Controller now has the
     * AuthorizesRequests trait (see app/Http/Controllers/Controller.php).
     */
    public function picReport(Request $request, Disbursement $disbursement): Response
    {
        $this->authorize('printReport', $disbursement);

        $pdf = $this->service->generatePicPdf($disbursement);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-' . \Str::slug($disbursement->name) . '.pdf"',
        ]);
    }

    /**
     * Download ZIP of all proof files for a disbursement.
     *
     * This route is placed OUTSIDE the role:pic middleware group (same as
     * proof download) so that Superadmin and Auditor can download the ZIP.
     * It is secured by a temporary signed URL generated in ReportService.
     *
     * Route name: disbursements.proofs.zip  (no pic. prefix)
     * This matches exactly what ReportService::generatePicPdf() encodes in the QR code.
     */
    public function downloadProofsZip(Request $request, Disbursement $disbursement): BinaryFileResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'Link tidak valid atau sudah kedaluwarsa.');
        $this->authorize('printReport', $disbursement);

        $zipPath = $this->service->buildProofZip($disbursement);

        return response()
            ->download($zipPath, 'bukti-' . $disbursement->id . '.zip')
            ->deleteFileAfterSend(true);
    }
}
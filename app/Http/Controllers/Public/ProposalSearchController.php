<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProposalSearchController extends Controller
{
    public function index(Request $request): Response
    {
        $results = null;

        if ($request->filled('q')) {
            $q = $request->q;
            $results = Proposal::with(['applicant:id,name', 'pic:id,name', 'approvers.user'])
                ->where('code', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($p) => $this->publicPayload($p));
        }

        return Inertia::render('Public/ProposalSearch', [
            'results' => $results,
            'query'   => $request->q,
        ]);
    }

    public function show(string $code): Response
    {
        $proposal = Proposal::where('code', $code)
            ->with(['applicant:id,name', 'pic:id,name', 'approvers.user'])
            ->firstOrFail();

        return Inertia::render('Public/ProposalSearch', [
            'results' => [$this->publicPayload($proposal)],
            'query'   => $code,
        ]);
    }

    public function downloadCertificate(Proposal $proposal): mixed
    {
        abort_unless(
            $proposal->isApproved()
            && $proposal->certificate_pdf_path
            && Storage::exists($proposal->certificate_pdf_path),
            404,
            'Pengesahan belum tersedia.'
        );

        return response()->streamDownload(
            fn () => print(Storage::get($proposal->certificate_pdf_path)),
            'sertifikat-' . $proposal->code . '.pdf',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    private function publicPayload(Proposal $p): array
    {
        return [
            'id'              => $p->id,
            'code'            => $p->code,
            'name'            => $p->name,
            'purpose'         => $p->purpose_label,
            'applicant_name'  => $p->applicant?->name ?? '-',
            'pic_name'        => $p->pic?->name ?? '-',
            'chairperson'     => $p->chairperson,
            'proposed_amount' => $p->proposed_amount,
            'approved_amount' => $p->approved_amount,
            'status'          => $p->status->value,
            'status_label'    => $p->status->label(),
            'status_color'    => $p->status->color(),
            'start_date'      => $p->start_date->format('d/m/Y'),
            'end_date'        => $p->end_date->format('d/m/Y'),
            'approved_at'     => $p->approved_at?->format('d F Y'),
            'has_certificate' => $p->isApproved() && (bool) $p->certificate_pdf_path,
            'approvers'       => $p->approvers->map(fn ($pa) => [
                'name'        => $pa->user?->name ?? '-',
                'approved'    => $pa->hasApproved(),
                'approved_at' => $pa->approved_at?->format('d/m/Y'),
            ])->values(),
        ];
    }
}

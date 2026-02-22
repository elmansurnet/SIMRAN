<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Proposal;
use App\Models\User;
use App\Services\ProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProposalController extends Controller
{
    public function __construct(private ProposalService $service) {}

    public function index(Request $request): Response
    {
        $proposals = Proposal::with(['applicant:id,name', 'pic:id,name'])
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")
            )
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($p) => $this->proposalRow($p));

        $approvers = User::where('role', UserRole::Approver)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Proposals/Index', [
            'proposals' => $proposals,
            'approvers' => $approvers,
            'filters'   => $request->only('search', 'status'),
        ]);
    }

    public function show(Proposal $proposal): Response
    {
        $proposal->load(['applicant', 'pic', 'approvers.user', 'reviewer', 'disbursement']);

        $approvers = User::where('role', UserRole::Approver)->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Proposals/Show', [
            'proposal'  => $this->proposalDetail($proposal),
            'approvers' => $approvers,
        ]);
    }

    public function forward(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->isDraft(), 422, 'Proposal sudah diproses.');

        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1',
                'max:' . $proposal->proposed_amount],
            'approver_ids'    => ['required', 'array', 'min:1'],
            'approver_ids.*'  => ['exists:users,id'],
            'note'            => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->forward(
            $proposal,
            $validated['approved_amount'],
            $validated['approver_ids'],
            $validated['note'] ?? null
        );

        return back()->with('success', 'Proposal berhasil diteruskan ke approver.');
    }

    public function reject(Request $request, Proposal $proposal): RedirectResponse
    {
        abort_unless($proposal->isDraft(), 422, 'Proposal sudah diproses.');

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->reject($proposal, $validated['note']);

        return back()->with('success', 'Proposal ditolak.');
    }

    public function downloadProposalPdf(Proposal $proposal): mixed
    {
        abort_unless($proposal->proposal_pdf_path && Storage::exists($proposal->proposal_pdf_path), 404);

        return response()->streamDownload(
            fn () => print(Storage::get($proposal->proposal_pdf_path)),
            'proposal-' . $proposal->code . '.pdf',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    public function downloadCertificate(Proposal $proposal): mixed
    {
        abort_unless($proposal->isApproved() && $proposal->certificate_pdf_path
            && Storage::exists($proposal->certificate_pdf_path), 404);

        return response()->streamDownload(
            fn () => print(Storage::get($proposal->certificate_pdf_path)),
            'sertifikat-' . $proposal->code . '.pdf',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    // ── Private helpers ───────────────────────────────

    private function proposalRow(Proposal $p): array
    {
        return [
            'id'              => $p->id,
            'code'            => $p->code,
            'name'            => $p->name,
            'purpose'         => $p->purpose_label,
            'purpose_value'   => $p->purpose,
            'applicant_name'  => $p->applicant?->name ?? '-',
            'pic_name'        => $p->pic?->name ?? '-',
            'proposed_amount' => $p->proposed_amount,
            'approved_amount' => $p->approved_amount,
            'status'          => $p->status->value,
            'status_label'    => $p->status->label(),
            'status_color'    => $p->status->color(),
            'start_date'      => $p->start_date->format('d/m/Y'),
            'end_date'        => $p->end_date->format('d/m/Y'),
            'created_at'      => $p->created_at->format('d/m/Y'),
        ];
    }

    private function proposalDetail(Proposal $p): array
    {
        return [
            ...$this->proposalRow($p),
            'chairperson'         => $p->chairperson,
            'superadmin_note'     => $p->superadmin_note,
            'forwarded_at'        => $p->forwarded_at?->format('d/m/Y H:i'),
            'approved_at'         => $p->approved_at?->format('d/m/Y H:i'),
            'reviewer_name'       => $p->reviewer?->name,
            'has_proposal_pdf'    => (bool) $p->proposal_pdf_path,
            'has_certificate'     => $p->isApproved() && (bool) $p->certificate_pdf_path,
            'approvers'           => $p->approvers->map(fn ($pa) => [
                'id'          => $pa->user_id,
                'name'        => $pa->user?->name ?? '-',
                'email'       => $pa->user?->email ?? '-',
                'approved_at' => $pa->approved_at?->format('d/m/Y H:i'),
                'note'        => $pa->note,
                'approved'    => $pa->hasApproved(),
            ])->values(),
        ];
    }
}

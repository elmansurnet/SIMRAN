<?php

namespace App\Http\Controllers\Approver;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalApprover;
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
        /**
         * WHY @var int $userId:
         *   auth()->id() returns int|string|null per the Guard interface.
         *   Inside a paginate()->through() closure, Intelephense may flag
         *   "Undefined method 'id'" when it cannot trace the Guard type.
         *   Capturing and annotating the value before the query removes the
         *   ambiguity without suppressing any real runtime error.
         *
         * @var int $userId
         */
        $userId = auth()->id();

        $items = ProposalApprover::with(['proposal.applicant', 'proposal.pic'])
            ->where('user_id', $userId)
            ->when($request->status === 'pending',  fn ($q) => $q->whereNull('approved_at'))
            ->when($request->status === 'approved', fn ($q) => $q->whereNotNull('approved_at'))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($pa) => [
                'id'              => $pa->proposal->id,
                'code'            => $pa->proposal->code,
                'name'            => $pa->proposal->name,
                'purpose'         => $pa->proposal->purpose_label,
                'applicant_name'  => $pa->proposal->applicant?->name ?? '-',
                'proposed_amount' => (float) $pa->proposal->proposed_amount,
                'approved_amount' => $pa->proposal->approved_amount !== null
                    ? (float) $pa->proposal->approved_amount
                    : null,
                'status'          => $pa->proposal->status->value,
                'status_label'    => $pa->proposal->status->label(),
                'status_color'    => $pa->proposal->status->color(),
                'my_approved_at'  => $pa->approved_at?->format('d/m/Y H:i'),
                'my_approved'     => $pa->hasApproved(),
            ]);

        return Inertia::render('Approver/Proposals/Index', [
            'items'   => $items,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Proposal $proposal): Response
    {
        /** @var int $userId */
        $userId = auth()->id();

        /** @var ProposalApprover|null $pa */
        $pa = ProposalApprover::where('proposal_id', $proposal->id)
            ->where('user_id', $userId)
            ->first();

        abort_if(! $pa, 403, 'Anda tidak ditugaskan untuk menyetujui proposal ini.');

        $proposal->load(['applicant', 'pic', 'approvers.user']);

        return Inertia::render('Approver/Proposals/Show', [
            'proposal' => [
                'id'               => $proposal->id,
                'code'             => $proposal->code,
                'name'             => $proposal->name,
                'purpose'          => $proposal->purpose_label,
                'chairperson'      => $proposal->chairperson,
                'applicant_name'   => $proposal->applicant?->name ?? '-',
                'pic_name'         => $proposal->pic?->name ?? '-',
                'proposed_amount'  => (float) $proposal->proposed_amount,
                'approved_amount'  => $proposal->approved_amount !== null ? (float) $proposal->approved_amount : null,
                'superadmin_note'  => $proposal->superadmin_note,
                'status'           => $proposal->status->value,
                'status_label'     => $proposal->status->label(),
                'status_color'     => $proposal->status->color(),
                'start_date'       => $proposal->start_date->format('d/m/Y'),
                'end_date'         => $proposal->end_date->format('d/m/Y'),
                'forwarded_at'     => $proposal->forwarded_at?->format('d/m/Y H:i'),
                'has_proposal_pdf' => (bool) $proposal->proposal_pdf_path,
                'approvers'        => $proposal->approvers->map(fn ($a) => [
                    'name'        => $a->user?->name ?? '-',
                    'approved'    => $a->hasApproved(),
                    'approved_at' => $a->approved_at?->format('d/m/Y'),
                    'note'        => $a->note,
                ])->values(),
            ],
            'my_approval' => [
                'approved'    => $pa->hasApproved(),
                'approved_at' => $pa->approved_at?->format('d/m/Y H:i'),
                'note'        => $pa->note,
            ],
        ]);
    }

    public function approve(Request $request, Proposal $proposal): RedirectResponse
    {
        /** @var int $userId */
        $userId = auth()->id();

        /** @var ProposalApprover|null $pa */
        $pa = ProposalApprover::where('proposal_id', $proposal->id)
            ->where('user_id', $userId)
            ->first();

        abort_if(! $pa, 403, 'Anda tidak ditugaskan untuk menyetujui proposal ini.');
        abort_if($pa->hasApproved(), 422, 'Anda sudah menyetujui proposal ini.');
        abort_unless($proposal->isForwarded(), 422, 'Proposal belum siap untuk disetujui.');

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->approve($proposal, $userId, $validated['note'] ?? null);

        return back()->with('success', 'Proposal berhasil disetujui.');
    }

    public function downloadProposalPdf(Proposal $proposal): mixed
    {
        /** @var int $userId */
        $userId = auth()->id();

        abort_unless(
            ProposalApprover::where('proposal_id', $proposal->id)
                ->where('user_id', $userId)->exists(),
            403
        );
        abort_unless(
            $proposal->proposal_pdf_path && Storage::exists($proposal->proposal_pdf_path),
            404
        );

        return response()->streamDownload(
            fn () => print(Storage::get($proposal->proposal_pdf_path)),
            'proposal-' . $proposal->code . '.pdf',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }
}
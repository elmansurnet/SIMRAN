<?php

namespace App\Http\Controllers\Applicant;

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
        $proposals = Proposal::where('applicant_id', auth()->id())
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")
            )
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($p) => [
                'id'              => $p->id,
                'code'            => $p->code,
                'name'            => $p->name,
                'purpose'         => $p->purpose_label,
                'proposed_amount' => $p->proposed_amount,
                'approved_amount' => $p->approved_amount,
                'status'          => $p->status->value,
                'status_label'    => $p->status->label(),
                'status_color'    => $p->status->color(),
                'start_date'      => $p->start_date->format('d/m/Y'),
                'end_date'        => $p->end_date->format('d/m/Y'),
                'created_at'      => $p->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Applicant/Proposals/Index', [
            'proposals' => $proposals,
            'filters'   => $request->only('search', 'status'),
        ]);
    }

    public function create(): Response
    {
        $pics = User::where('role', UserRole::Pic)->get(['id', 'name', 'email']);

        return Inertia::render('Applicant/Proposals/Create', [
            'pics' => $pics,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purpose'         => ['required', 'in:activity,operational'],
            'name'            => ['required', 'string', 'max:255'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'pic_id'          => ['required', 'exists:users,id'],
            'chairperson'     => ['required', 'string', 'max:255'],
            'proposed_amount' => ['required', 'numeric', 'min:1'],
            'proposal_pdf'    => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $proposal = $this->service->create($validated, $request->file('proposal_pdf'));

        return redirect()
            ->route('applicant.proposals.show', $proposal)
            ->with('success', "Proposal {$proposal->code} berhasil diajukan.");
    }

    public function show(Proposal $proposal): Response
    {
        // Ensure applicant only sees their own proposals
        abort_unless($proposal->applicant_id === auth()->id(), 403);

        $proposal->load(['pic', 'approvers.user']);

        return Inertia::render('Applicant/Proposals/Show', [
            'proposal' => [
                'id'              => $proposal->id,
                'code'            => $proposal->code,
                'name'            => $proposal->name,
                'purpose'         => $proposal->purpose_label,
                'chairperson'     => $proposal->chairperson,
                'pic_name'        => $proposal->pic?->name ?? '-',
                'proposed_amount' => $proposal->proposed_amount,
                'approved_amount' => $proposal->approved_amount,
                'superadmin_note' => $proposal->superadmin_note,
                'status'          => $proposal->status->value,
                'status_label'    => $proposal->status->label(),
                'status_color'    => $proposal->status->color(),
                'start_date'      => $proposal->start_date->format('d/m/Y'),
                'end_date'        => $proposal->end_date->format('d/m/Y'),
                'created_at'      => $proposal->created_at->format('d/m/Y H:i'),
                'forwarded_at'    => $proposal->forwarded_at?->format('d/m/Y H:i'),
                'approved_at'     => $proposal->approved_at?->format('d/m/Y H:i'),
                'has_proposal_pdf' => (bool) $proposal->proposal_pdf_path,
                'has_certificate'  => $proposal->isApproved() && (bool) $proposal->certificate_pdf_path,
                'approvers'        => $proposal->approvers->map(fn ($pa) => [
                    'name'        => $pa->user?->name ?? '-',
                    'approved'    => $pa->hasApproved(),
                    'approved_at' => $pa->approved_at?->format('d/m/Y'),
                ])->values(),
            ],
        ]);
    }

    public function downloadCertificate(Proposal $proposal): mixed
    {
        abort_unless($proposal->applicant_id === auth()->id(), 403);
        abort_unless($proposal->isApproved() && $proposal->certificate_pdf_path
            && Storage::exists($proposal->certificate_pdf_path), 404);

        return response()->streamDownload(
            fn () => print(Storage::get($proposal->certificate_pdf_path)),
            'sertifikat-' . $proposal->code . '.pdf',
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }
}

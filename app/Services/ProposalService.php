<?php

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\ProposalApprover;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProposalService
{
    public function __construct(private ReportService $reports) {}

    /**
     * Applicant submits a new proposal.
     *
     * WHY @var annotation on $actorId:
     *   auth()->id() is declared as returning int|string|null on the Guard interface.
     *   Inside DB::transaction() closures, Intelephense may report "Undefined method 'id'"
     *   because it loses the Auth facade → Guard → StatefulGuard type chain.
     *   Capturing the ID before entering the closure and annotating its type removes
     *   the ambiguity without suppressing any real error.
     */
    public function create(array $data, ?UploadedFile $pdf = null): Proposal
    {
        /** @var int $actorId */
        $actorId = auth()->id();

        return DB::transaction(function () use ($data, $pdf, $actorId) {
            $proposal = Proposal::create([
                'code'            => Proposal::generateCode(),
                'status'          => ProposalStatus::Draft,
                'applicant_id'    => $actorId,
                'purpose'         => $data['purpose'],
                'name'            => $data['name'],
                'start_date'      => $data['start_date'],
                'end_date'        => $data['end_date'],
                'pic_id'          => $data['pic_id'],
                'chairperson'     => $data['chairperson'],
                'proposed_amount' => $data['proposed_amount'],
            ]);

            if ($pdf) {
                $path = $pdf->store("proposals/{$proposal->id}", 'local');
                $proposal->update(['proposal_pdf_path' => $path]);
            }

            return $proposal;
        });
    }

    /**
     * Superadmin sets approved_amount and forwards to selected approvers.
     */
    public function forward(Proposal $proposal, float $approvedAmount, array $approverIds, ?string $note = null): void
    {
        /** @var int $actorId */
        $actorId = auth()->id();

        DB::transaction(function () use ($proposal, $approvedAmount, $approverIds, $note, $actorId) {
            $proposal->approvers()->delete();

            foreach ($approverIds as $userId) {
                ProposalApprover::create([
                    'proposal_id' => $proposal->id,
                    'user_id'     => $userId,
                ]);
            }

            $proposal->update([
                'status'          => ProposalStatus::Forwarded,
                'approved_amount' => $approvedAmount,
                'reviewed_by'     => $actorId,
                'forwarded_at'    => now(),
                'superadmin_note' => $note,
            ]);
        });
    }

    /** Superadmin rejects a proposal. */
    public function reject(Proposal $proposal, ?string $note = null): void
    {
        /** @var int $actorId */
        $actorId = auth()->id();

        $proposal->update([
            'status'          => ProposalStatus::Rejected,
            'reviewed_by'     => $actorId,
            'superadmin_note' => $note,
        ]);
    }

    /**
     * Approver records their approval.
     * If ALL approvers have approved → proposal becomes Approved + certificate generated.
     */
    public function approve(Proposal $proposal, int $approverId, ?string $note = null): void
    {
        DB::transaction(function () use ($proposal, $approverId, $note) {
            /** @var ProposalApprover $pa */
            $pa = ProposalApprover::where('proposal_id', $proposal->id)
                ->where('user_id', $approverId)
                ->firstOrFail();

            $pa->update(['approved_at' => now(), 'note' => $note]);

            $proposal->refresh();
            if ($proposal->allApproved()) {
                $certPdf  = $this->reports->generateApprovalCertificate($proposal);
                $certPath = "proposals/{$proposal->id}/certificate.pdf";
                Storage::put($certPath, $certPdf);

                $proposal->update([
                    'status'               => ProposalStatus::Approved,
                    'approved_at'          => now(),
                    'certificate_pdf_path' => $certPath,
                ]);
            }
        });
    }
}
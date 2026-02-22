<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                          // e.g. PROP-2025-0001
            $table->enum('status', [
                'draft',           // submitted by applicant, not yet reviewed
                'forwarded',       // superadmin forwarded to approvers
                'approved',        // all approvers approved
                'rejected',        // superadmin or approver rejected
            ])->default('draft');

            // Applicant
            $table->foreignId('applicant_id')->constrained('users');

            // Activity details
            $table->enum('purpose', ['activity', 'operational']);
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('pic_id')->constrained('users');
            $table->string('chairperson');

            // Budget
            $table->decimal('proposed_amount', 18, 2);
            $table->decimal('approved_amount', 18, 2)->nullable();

            // Files
            $table->string('proposal_pdf_path')->nullable();           // uploaded PDF
            $table->string('certificate_pdf_path')->nullable();        // generated cert

            // Superadmin action
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('forwarded_at')->nullable();
            $table->text('superadmin_note')->nullable();

            // Approval completion
            $table->timestamp('approved_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('proposal_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');       // must have role = approver
            $table->timestamp('approved_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['proposal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_approvers');
        Schema::dropIfExists('proposals');
    }
};

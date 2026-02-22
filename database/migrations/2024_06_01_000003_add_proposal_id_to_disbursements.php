<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disbursements', function (Blueprint $table) {
            $table->foreignId('proposal_id')
                  ->nullable()
                  ->after('parent_disbursement_id')
                  ->constrained('proposals')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disbursements', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Proposal::class);
            $table->dropColumn('proposal_id');
        });
    }
};

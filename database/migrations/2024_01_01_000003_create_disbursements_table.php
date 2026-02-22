<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->enum('purpose', ['activity', 'operational']);
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('pic_id')->constrained('users');
            $table->string('chairperson');
            $table->foreignId('budget_allocation_id')->constrained('budget_allocations');
            $table->decimal('amount', 18, 2);
            $table->boolean('auto_generate')->default(false);
            $table->foreignId('parent_disbursement_id')->nullable()->constrained('disbursements')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('disbursements'); }
};

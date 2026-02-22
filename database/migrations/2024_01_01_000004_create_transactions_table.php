<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_id')->constrained('disbursements');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('type', ['expense', 'income']);
            $table->date('transaction_date');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->string('proof_path', 500)->nullable();
            $table->string('proof_original_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transactions'); }
};

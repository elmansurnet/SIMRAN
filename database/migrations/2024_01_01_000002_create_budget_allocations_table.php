<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->boolean('auto_generate')->default(false);
            $table->foreignId('parent_allocation_id')->nullable()->constrained('budget_allocations')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('budget_allocations'); }
};

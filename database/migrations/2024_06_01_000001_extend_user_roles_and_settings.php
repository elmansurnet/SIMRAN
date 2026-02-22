<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the role enum to include new roles
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'superadmin','pic','auditor','applicant','approver'
        ) NOT NULL DEFAULT 'applicant'");

        // Settings key-value store
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('settings')->insert([
            ['key' => 'extra_transaction_days', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_name',               'value' => 'SIMRAN UNISYA',            'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'superadmin','pic','auditor'
        ) NOT NULL DEFAULT 'pic'");

        Schema::dropIfExists('settings');
    }
};

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
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'superadmin','pic','auditor'
        ) NOT NULL DEFAULT 'pic'");

    }
};

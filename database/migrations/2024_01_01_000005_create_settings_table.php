<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed sensible defaults so the app works immediately
        DB::table('settings')->insert([
            ['key' => 'extra_transaction_days', 'value' => '0',            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_name',               'value' => 'SIMRAN UNISYA', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
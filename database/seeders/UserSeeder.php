<?php
namespace Database\Seeders;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
class UserSeeder extends Seeder {
    public function run(): void {
        User::firstOrCreate(['email' => 'superadmin@unisya.ac.id'], [
            'name'     => 'Super Administrator',
            'password' => 'password123',
            'role'     => UserRole::Superadmin,
        ]);
        User::firstOrCreate(['email' => 'pic@unisya.ac.id'], [
            'name'     => 'Ahmad Fauzi',
            'password' => 'password123',
            'role'     => UserRole::Pic,
        ]);
        User::firstOrCreate(['email' => 'auditor@unisya.ac.id'], [
            'name'     => 'Budi Santoso',
            'password' => 'password123',
            'role'     => UserRole::Auditor,
        ]);
        User::firstOrCreate(['email' => 'approver@unisya.ac.id'], [
            'name'     => 'Rektor 3',
            'password' => 'password123',
            'role'     => UserRole::Approver,
        ]);
        User::firstOrCreate(['email' => 'applicant@unisya.ac.id'], [
            'name'     => 'Mahasiswa PAI',
            'password' => 'password123',
            'role'     => UserRole::Applicant,
        ]);
    }
}

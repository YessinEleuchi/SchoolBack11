<?php
namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin user in the `users` table
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(), // Add this
            'password' => Hash::make('123456'),
            'role' => RoleEnum::Admin->value,
            'gender' => 'male',
            'phone' => '1234567890',
            'address' => '123 Admin Street',
            'date_of_birth' => '1990-01-01',
        ]);

        // Create the corresponding admin entry in the `admins` table
        Admin::create([
            'user_id' => $user->id,
            'admission_no' => 'ADMIN001',
        ]);
    }
}
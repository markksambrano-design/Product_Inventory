<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            HouseholdEssentialsSeeder::class,
            HouseholdEssentialsStockSeeder::class,
            SampleInventorySeeder::class,
        ]);

        $accounts = [
            ['email' => 'admin@inventory.com', 'name' => 'Inventory Admin', 'role' => 'admin', 'password' => env('DEFAULT_ADMIN_PASSWORD') ?: 'Admin@12345'],
            ['email' => 'staff@inventory.com', 'name' => 'Inventory Staff', 'role' => 'staff', 'password' => env('DEFAULT_STAFF_PASSWORD') ?: 'Staff@12345'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}

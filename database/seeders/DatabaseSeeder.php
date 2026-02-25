<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@9mint.store'], // Check if exists
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Default dev password
                
            ]
        );

        // force the role securely
        $admin->role = 'admin';
        $admin->save();

        // Dummy Customer for testing
        User::firstOrCreate(
            ['email' => 'customer@9mint.store'],
            [
                'name' => 'testcustomer',
                'password' => Hash::make('password'),
            ]
        );
        // User::factory(10)->create();

        $this->call([
            DemoSeeder::class,
            CurrencySeeder::class,
        ]);
    }
}

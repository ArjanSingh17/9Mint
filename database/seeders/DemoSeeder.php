<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nineMint = User::updateOrCreate(
            ['id' => 1],
            ['name' => '9Mint', 'email' => null, 'password' => null, 'role' => 'admin']
        );

        $vlas = User::updateOrCreate(
            ['id' => 2],
            ['name' => 'Vlas', 'email' => null, 'password' => null, 'role' => 'user']
        );

        try {
            Role::firstOrCreate(['name' => 'admin']);
            $nineMint->assignRole('admin');
        } catch (\Throwable $e) {
            // Ignore role assignment failures.
        }
    }
}

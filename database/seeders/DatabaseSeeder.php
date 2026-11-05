<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@donasogra.com'],
            [
                'name' => 'Admin Dona Sogra',
                'password' => bcrypt('admin@2026'),
            ]
        );

        $this->call(ProdutoSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'santosiurysousa@gmail.com'],
            [
                'name' => 'Iury Sousa',
                'password' => Hash::make('Aa@123456'),
                'user_type' => 'owner',
                'email_verified_at' => now(),
            ]
        );
    }
}

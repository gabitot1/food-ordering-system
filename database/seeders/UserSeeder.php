<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tso_dev@unilab.com.ph'],
            [
                'name' => 'admin',
                'password' => bcrypt('w0rd10p@ss'),
                'is_admin' => true,
            ]
        );
    }
}

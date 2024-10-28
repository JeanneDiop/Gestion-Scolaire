<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            'nom' => 'admin',
            'prenom' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('passer1234'),
            'role_nom' => 'admin',
            'adresse' => 'admin',
            'telephone' => '+221778009876',
            'genre' => 'Femme'
        ];
        $user = User::create($userData);

        $admin = $user->admin()->create();
    }
}

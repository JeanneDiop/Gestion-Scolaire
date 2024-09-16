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
            'nom' => 'WADE',
            'prenom' => 'Mariam',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('passer1234'),
            'role_nom' => 'admin',
            'adresse' => 'sicap',
            'telephone' => '+221778009876',
            'genre' => 'femme'
        ];
        $user = User::create($userData);

        $admin = $user->admin()->create();
    }
}

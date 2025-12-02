<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run()
    {
        Client::create([
            'email' => 'client@example.com',
            'password' => Hash::make('password123'), // hashed password
        ]);
    }
}

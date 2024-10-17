<?php

namespace Database\Seeders;

use App\Models\Addresses;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Mesa;
use App\Models\User;
use Database\Factories\ClientFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Mesa::factory(30)->create();

        Client::factory(10)->create();

        Addresses::factory(10)->create();

        ClientAddress::factory(10)->create();
    }
}

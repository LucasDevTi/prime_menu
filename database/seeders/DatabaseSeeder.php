<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
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

        Client::factory(10)->create();
        Address::factory(10)->create();

    }
}

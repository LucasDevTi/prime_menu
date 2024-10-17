<?php

namespace Database\Factories;

use App\Models\Addresses;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientAddress>
 */
class ClientAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $client = Client::inRandomOrder()->first();
        $address = Addresses::inRandomOrder()->first();

        return [
            'client_id' => $client['id'],
            'address_id' => $address['id'],
            'main' => fake()->boolean()
        ];
    }
}

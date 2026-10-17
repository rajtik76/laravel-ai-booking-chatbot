<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\City;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        Hotel::factory()->createMany([
            ['name' => 'Hotel Praha', 'city' => City::Prague],
            ['name' => 'Hotel Paris', 'city' => City::Paris],
            ['name' => 'Hotel Berlin', 'city' => City::Berlin],
        ]);
    }
}

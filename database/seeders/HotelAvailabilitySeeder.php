<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelAvailability;
use Illuminate\Database\Seeder;

class HotelAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Hotel::all() as $hotel) {
            HotelAvailability::factory()
                ->for($hotel)
                ->create(['from' => now(), 'to' => now()->addYear()->endOfYear()]);
        }
    }
}

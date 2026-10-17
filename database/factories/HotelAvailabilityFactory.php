<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\HotelAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<HotelAvailability>
 */
class HotelAvailabilityFactory extends Factory
{
    protected $model = HotelAvailability::class;

    public function definition(): array
    {
        $from = CarbonImmutable::instance($this->faker->dateTimeBetween('now', '+6 months'))->startOfDay();

        return [
            'from' => $from,
            'to' => $from->addDays($this->faker->numberBetween(1, 60)),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'hotel_id' => Hotel::factory(),
        ];
    }
}

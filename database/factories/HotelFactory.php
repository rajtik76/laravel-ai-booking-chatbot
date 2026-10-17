<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\City;
use App\Enums\Country;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'city' => $this->faker->randomElement(City::class),
            'country' => fn (array $attributes): Country => ($attributes['city'] instanceof City ? $attributes['city'] : City::from($attributes['city']))->country(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}

<?php

declare(strict_types=1);

use App\Enums\City;
use App\Models\Hotel;
use App\Models\HotelAvailability;

it('derives the hotel country from its city', function (City $city) {
    expect(Hotel::factory()->create(['city' => $city])->country)->toBe($city->country());
})->with(City::cases());

it('creates availability that ends after it starts', function () {
    $availability = HotelAvailability::factory()->create();

    expect($availability->to->greaterThan($availability->from))->toBeTrue();
});

it('deletes availabilities together with the hotel', function () {
    $hotel = Hotel::factory()->has(HotelAvailability::factory(), 'availabilities')->create();

    $hotel->delete();

    expect(HotelAvailability::query()->count())->toBe(0);
});

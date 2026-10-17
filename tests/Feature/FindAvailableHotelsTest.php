<?php

declare(strict_types=1);

use App\Ai\Tools\FindAvailableHotels;
use App\Enums\City;
use App\Models\Hotel;
use App\Models\HotelAvailability;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Tools\Request;

use function Pest\Laravel\travelTo;

beforeEach(function () {
    travelTo('2026-10-17 12:00:00');
});

/**
 * @param  array<string, string>  $arguments
 */
function findHotels(array $arguments, string $timezone = 'UTC'): string
{
    return (string) (new FindAvailableHotels($timezone))->handle(new Request($arguments));
}

/**
 * @param  array{string, string}  ...$windows
 */
function hotelAvailableBetween(string $name, City $city, array ...$windows): Hotel
{
    $hotel = Hotel::factory()->create(['name' => $name, 'city' => $city]);

    foreach ($windows as [$from, $to]) {
        HotelAvailability::factory()->for($hotel)->create(['from' => $from, 'to' => $to]);
    }

    return $hotel;
}

it('returns hotels whose availability covers the whole stay', function () {
    hotelAvailableBetween('Covered', City::Prague, ['2026-11-01', '2026-11-30']);
    hotelAvailableBetween('Ends too early', City::Prague, ['2026-11-01', '2026-11-05']);
    hotelAvailableBetween('Other city', City::Paris, ['2026-11-01', '2026-11-30']);

    $result = json_decode(findHotels([
        'city' => 'Prague',
        'check_in' => '2026-11-03',
        'check_out' => '2026-11-07',
    ]), true);

    expect($result)->toBe([
        ['name' => 'Covered', 'city' => 'Prague', 'country' => 'CZ'],
    ]);
});

it('includes stays that touch the availability boundaries', function () {
    hotelAvailableBetween('Exact fit', City::Berlin, ['2026-11-01', '2026-11-05']);

    expect(findHotels([
        'city' => 'Berlin',
        'check_in' => '2026-11-01',
        'check_out' => '2026-11-05',
    ]))->toContain('Exact fit');
});

it('includes stays covered by consecutive availability windows', function () {
    hotelAvailableBetween('Split', City::Prague, ['2026-11-06', '2026-11-10'], ['2026-11-01', '2026-11-05']);

    expect(findHotels([
        'city' => 'Prague',
        'check_in' => '2026-11-03',
        'check_out' => '2026-11-08',
    ]))->toContain('Split');
});

it('excludes stays with a gap between availability windows', function () {
    hotelAvailableBetween('Gap', City::Prague, ['2026-11-01', '2026-11-04'], ['2026-11-06', '2026-11-10']);

    expect(findHotels([
        'city' => 'Prague',
        'check_in' => '2026-11-03',
        'check_out' => '2026-11-08',
    ]))->not->toContain('Gap');
});

it('tells the model when nothing is available', function () {
    expect(findHotels([
        'city' => 'Berlin',
        'check_in' => '2026-11-01',
        'check_out' => '2026-11-05',
    ]))->toBe('No hotels in Berlin are available from 2026-11-01 to 2026-11-05.');
});

it('rejects invalid arguments', function (array $arguments) {
    findHotels($arguments);
})->with([
    'unknown city' => [['city' => 'London', 'check_in' => '2026-11-01', 'check_out' => '2026-11-05']],
    'check-out before check-in' => [['city' => 'Prague', 'check_in' => '2026-11-05', 'check_out' => '2026-11-01']],
    'bad date format' => [['city' => 'Prague', 'check_in' => '1. 11. 2026', 'check_out' => '2026-11-05']],
    'check-in in the past' => [['city' => 'Prague', 'check_in' => '2026-10-16', 'check_out' => '2026-10-20']],
])->throws(ValidationException::class);

it('resolves today in the guest timezone', function () {
    travelTo('2026-10-17 23:30:00');

    $arguments = ['city' => 'Prague', 'check_in' => '2026-10-17', 'check_out' => '2026-10-20'];

    expect(findHotels($arguments, 'UTC'))->toStartWith('No hotels')
        ->and(fn () => findHotels($arguments, 'Europe/Prague'))->toThrow(ValidationException::class);
});

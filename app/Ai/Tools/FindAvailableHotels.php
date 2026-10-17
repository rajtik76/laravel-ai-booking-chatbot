<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Enums\City;
use App\Models\Hotel;
use App\Models\HotelAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindAvailableHotels implements Tool
{
    public function __construct(private string $timezone = 'UTC') {}

    public function description(): Stringable|string
    {
        return 'Find hotels in a city that are available for the whole stay between check-in and check-out.';
    }

    public function handle(Request $request): Stringable|string
    {
        /** @var array{city: string, check_in: string, check_out: string} $validated */
        $validated = $request->validate([
            'city' => ['required', Rule::enum(City::class)],
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now($this->timezone)->toDateString()],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
        ]);

        $checkIn = CarbonImmutable::parse($validated['check_in']);
        $checkOut = CarbonImmutable::parse($validated['check_out']);

        $hotels = Hotel::query()
            ->where('city', $validated['city'])
            ->withWhereHas('availabilities', fn (Builder $query): Builder => $query
                ->whereDate('from', '<=', $checkOut)
                ->whereDate('to', '>=', $checkIn)
                ->orderBy('from'))
            ->orderBy('name')
            ->get()
            ->filter(fn (Hotel $hotel): bool => $this->coversStay($hotel->availabilities, $checkIn, $checkOut));

        if ($hotels->isEmpty()) {
            return "No hotels in {$validated['city']} are available from {$validated['check_in']} to {$validated['check_out']}.";
        }

        return $hotels
            ->map(fn (Hotel $hotel): array => [
                'name' => $hotel->name,
                'city' => $hotel->city->value,
                'country' => $hotel->country->value,
            ])
            ->values()
            ->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()->enum(array_column(City::cases(), 'value'))->required(),
            'check_in' => $schema->string()->description('Check-in date in Y-m-d format.')->required(),
            'check_out' => $schema->string()->description('Check-out date in Y-m-d format.')->required(),
        ];
    }

    /**
     * @param  Collection<int, HotelAvailability>  $availabilities
     */
    private function coversStay(Collection $availabilities, CarbonImmutable $checkIn, CarbonImmutable $checkOut): bool
    {
        $firstUncoveredDay = $checkIn;

        foreach ($availabilities as $availability) {
            if ($availability->from->greaterThan($firstUncoveredDay)) {
                return false;
            }

            if ($availability->to->greaterThanOrEqualTo($firstUncoveredDay)) {
                $firstUncoveredDay = $availability->to->addDay();
            }

            if ($firstUncoveredDay->greaterThan($checkOut)) {
                return true;
            }
        }

        return false;
    }
}

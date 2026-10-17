<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\City;
use App\Enums\Country;
use Database\Factories\HotelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    /** @use HasFactory<HotelFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'country' => Country::class,
            'city' => City::class,
        ];
    }

    /** @return HasMany<HotelAvailability, $this> */
    public function availabilities(): HasMany
    {
        return $this->hasMany(HotelAvailability::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HotelAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelAvailability extends Model
{
    /** @use HasFactory<HotelAvailabilityFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    protected function casts(): array
    {
        return [
            'from' => 'immutable_date',
            'to' => 'immutable_date',
        ];
    }
}

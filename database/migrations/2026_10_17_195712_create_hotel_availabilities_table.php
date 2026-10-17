<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->date('from');
            $table->date('to');
            $table->timestamps();

            $table->index(['hotel_id', 'from', 'to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_availabilities');
    }
};

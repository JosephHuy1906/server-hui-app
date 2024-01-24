<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auction_hui_room', function (Blueprint $table) {
            $table->id();
            $table->integer('starting_price');
            $table->integer('auction_price')->default(0);
            $table->dateTime('time_end');
            $table->timestamps();
            $table->foreignId('room_id')->references('id')->on('rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_hui_room');
    }
};

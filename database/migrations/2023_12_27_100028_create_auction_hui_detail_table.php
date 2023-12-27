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
        Schema::create('auction_hui_detail', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->integer('starting_price');
            $table->float('auction_percentage');
            $table->float('total_price');
            $table->timestamps();
            $table->foreignId('auction_hui_id')->references('id')->on('auction_hui_room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_hui_detail');
    }
};

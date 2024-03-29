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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('price_room');
            $table->string('avatar')->nullable();
            $table->double('commission_percentage')->default(3);
            $table->enum('payment_time', ['End day', 'End of Month']);
            $table->enum('status', ['Open', 'Lock', 'Close']);
            $table->timestamp('date_room_end');
            $table->integer('total_user');
            $table->integer('accumulated_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

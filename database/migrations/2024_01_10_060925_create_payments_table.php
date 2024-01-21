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
        Schema::create('payments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id');
            $table->foreignId('room_user_id')->references("id")->on("room_user")->constrained();
            $table->foreign('user_id')->references("id")->on("users")->constrained();
            $table->foreignId('room_id')->references("id")->on("rooms")->constrained();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('description');
            $table->integer('price_pay');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

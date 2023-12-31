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
        Schema::create('check_out', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id');
            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->integer('price');
            $table->unsignedBigInteger('user_win_hui_id')->nullable();
            $table->foreign('user_win_hui_id')->references('id')->on('user_win_hui')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_out');
    }
};

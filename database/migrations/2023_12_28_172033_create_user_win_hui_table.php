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
        Schema::create('user_win_hui', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->double('commission_percentage');
            $table->integer('price_pay_hui');
            $table->integer('total_auction');
            $table->foreignId('room_id')->references('id')->on('rooms');
            $table->integer('total_money_received');
            $table->integer('total_amount_payable');
            $table->enum('status_admin', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_user', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_win_hui');
    }
};

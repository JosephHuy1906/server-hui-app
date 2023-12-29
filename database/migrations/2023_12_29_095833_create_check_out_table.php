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
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('price');
            $table->foreignId('user_win_hui_id')->references('id')->on('user_win_hui')->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');;
            $table->string('description');
            $table->enum('status', ['pending', 'approved', 'rejected']);
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

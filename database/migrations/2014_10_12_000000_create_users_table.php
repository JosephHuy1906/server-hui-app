<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('avatar')->nullable();
            $table->unsignedBigInteger('role')->default(3);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('cccd_after')->nullable();
            $table->text('cccd_before')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->integer('user_count')->default(0);
            $table->foreign('role')->references('id')->on('role')->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

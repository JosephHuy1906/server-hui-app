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
            $table->string('phone');
            $table->text('avatar')->nullable();
            $table->string('email')->unique();
            $table->string('birthday')->nullable();
            $table->enum('sex', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->text('address')->nullable();
            $table->enum('role', ['User', 'Admin', 'SubAdmin'])->default('User');
            $table->enum('rank', ['Thành viên mới', 'Thành viên bạc', 'Thành viên vàng', 'Thành viên kim cương'])->default('Thành viên mới');
            $table->string('password');
            $table->string('code')->nullable();
            $table->text('cccd_after')->nullable();
            $table->text('cccd_before')->nullable();
            $table->integer('user_count')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

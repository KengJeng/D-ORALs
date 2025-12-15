<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_history', function (Blueprint $table) {
            $table->id(); // id (BIGINT UNSIGNED)
            $table->unsignedBigInteger('user_id');   // id from admin or patient
            $table->string('user_type');             // 'admin' or 'patient'
            $table->dateTime('login_time')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->index(['user_id', 'user_type', 'login_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_history');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->integer('queue_number');
            $table->text('message');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('created_at');
            
            $table->index('appointment_id');
            $table->index('is_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
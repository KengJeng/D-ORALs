<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->enum('status', ['Pending', 'Confirmed', 'Completed', 'Canceled', 'No-show'])->default('Pending');
            $table->integer('queue_number')->nullable();
            $table->timestamps();
            
            $table->index(['scheduled_date', 'status']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
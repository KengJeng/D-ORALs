<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->date('log_date');
            $table->time('log_time');
            
            $table->index('user_id');
            $table->index('log_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditlog');
    }
};
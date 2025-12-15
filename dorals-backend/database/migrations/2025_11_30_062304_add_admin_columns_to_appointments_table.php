<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Admin who created the appointment
            $table->unsignedBigInteger('created_by')
                  ->nullable()
                  ->after('queue_number');

            // Admin who last updated it
            $table->unsignedBigInteger('updated_by')
                  ->nullable()
                  ->after('created_by');

            // Foreign keys to admin table
            $table->foreign('created_by')
                  ->references('admin_id')
                  ->on('admin')
                  ->onDelete('set null');

            $table->foreign('updated_by')
                  ->references('admin_id')
                  ->on('admin')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};

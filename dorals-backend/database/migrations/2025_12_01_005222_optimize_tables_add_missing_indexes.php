<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Notifications optimization
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(
                ['appointment_id', 'is_sent'],
                'idx_notif_appointment_sent'
            );

            $table->index(
                ['is_sent', 'queue_number'],
                'idx_notif_sent_queue'
            );
        });

        // 2. Patients optimization
        Schema::table('patients', function (Blueprint $table) {
            $table->index(
                ['last_name', 'first_name'],
                'idx_patients_last_first'
            );
        });

        // 3. Services optimization
        Schema::table('services', function (Blueprint $table) {
            $table->index('name', 'idx_services_name');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notif_appointment_sent');
            $table->dropIndex('idx_notif_sent_queue');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_last_first');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_name');
        });
    }
};

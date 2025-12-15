<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // AFTER INSERT trigger
        DB::unprepared("
            CREATE TRIGGER trg_appointments_after_insert
            AFTER INSERT ON appointments
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (user_id, action, log_date, log_time, created_at, updated_at)
                VALUES (
                    NULL,                                          -- later you can pass admin id via app if needed
                    CONCAT('Created appointment #', NEW.appointment_id),
                    CURDATE(),
                    CURTIME(),
                    NOW(),
                    NOW()
                );
            END
        ");

        // AFTER UPDATE trigger
        DB::unprepared("
            CREATE TRIGGER trg_appointments_after_update
            AFTER UPDATE ON appointments
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_log (user_id, action, log_date, log_time, created_at, updated_at)
                VALUES (
                    NULL,
                    CONCAT('Updated appointment #', NEW.appointment_id, ' status to ', NEW.status),
                    CURDATE(),
                    CURTIME(),
                    NOW(),
                    NOW()
                );
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_appointments_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_appointments_after_update');
    }
};

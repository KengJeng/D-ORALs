<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Make user_id nullable so we can set invalid ones to NULL
        DB::statement('ALTER TABLE audit_log MODIFY user_id BIGINT UNSIGNED NULL');

        // 2) Set any user_id that does NOT exist in admin.admin_id to NULL
        DB::statement("
            UPDATE audit_log al
            LEFT JOIN admin a ON a.admin_id = al.user_id
            SET al.user_id = NULL
            WHERE a.admin_id IS NULL
        ");

        // 3) Now add the foreign key with ON DELETE SET NULL
        Schema::table('audit_log', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('admin_id')
                  ->on('admin')        // actual table name in your DB
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('audit_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Optional: you can make user_id NOT NULL again if you really want
        // DB::statement('ALTER TABLE audit_log MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};

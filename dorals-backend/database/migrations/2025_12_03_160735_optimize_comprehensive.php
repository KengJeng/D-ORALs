<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations - Comprehensive optimization with indexes, partitioning strategy, and aggregation tables
     */
    public function up(): void
    {
        // 1. VERTICAL PARTITIONING: Create denormalized tables for heavy read operations
        
        // 2. APPOINTMENT STATS TABLE (Denormalization for Dashboard aggregations)
        if (!Schema::hasTable('appointment_stats')) {
            Schema::create('appointment_stats', function (Blueprint $table) {
                $table->id('stat_id');
                $table->date('stat_date')->index();
                $table->integer('total_appointments')->default(0);
                $table->integer('pending_count')->default(0);
                $table->integer('confirmed_count')->default(0);
                $table->integer('completed_count')->default(0);
                $table->integer('canceled_count')->default(0);
                $table->integer('no_show_count')->default(0);
                $table->decimal('completion_rate', 5, 2)->default(0);
                $table->timestamps();
                
                // Composite index for date-based queries
                $table->unique('stat_date', 'idx_appointment_stats_date');
            });
        }

        // 3. PATIENT STATS TABLE (Denormalization for Demographics)
        if (!Schema::hasTable('patient_stats')) {
            Schema::create('patient_stats', function (Blueprint $table) {
                $table->id('stat_id');
                $table->date('stat_date')->index();
                $table->integer('total_patients')->default(0);
                $table->integer('new_today')->default(0);
                $table->integer('active_patients')->default(0);
                $table->integer('male_count')->default(0);
                $table->integer('female_count')->default(0);
                $table->timestamps();
                
                $table->unique('stat_date', 'idx_patient_stats_date');
            });
        }

        // 4. HORIZONTAL PARTITIONING: Archive old appointments
        if (!Schema::hasTable('appointments_archive')) {
            Schema::create('appointments_archive', function (Blueprint $table) {
                $table->id('appointment_id');
                $table->unsignedBigInteger('patient_id');
                $table->date('scheduled_date')->index();
                $table->string('status')->index();
                $table->integer('queue_number')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                // Partition archive by year for better performance
                $table->index(['scheduled_date', 'status'], 'idx_archive_date_status');
                $table->index('patient_id', 'idx_archive_patient');
            });
        }

        // 5. LOGIN STATS TABLE (Denormalization for analytics)
        if (!Schema::hasTable('login_stats')) {
            Schema::create('login_stats', function (Blueprint $table) {
                $table->id('stat_id');
                $table->date('stat_date')->index();
                $table->string('user_type')->index(); // 'admin' or 'patient'
                $table->integer('login_count')->default(0);
                $table->integer('unique_users')->default(0);
                $table->timestamps();
                
                $table->unique(['stat_date', 'user_type'], 'idx_login_stats_date_type');
            });
        }

        // 6. ENHANCED INDEXES ON EXISTING TABLES

        // Appointments - add composite indexes for common queries
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            }
        });

        // Login History - add composite index for date-based filtering
        if (Schema::hasTable('login_history')) {
            Schema::table('login_history', function (Blueprint $table) {
                // Check if index doesn't exist
                try {
                    $table->index(
                        ['login_time', 'user_type'],
                        'idx_login_history_time_type'
                    );
                } catch (\Exception $e) {
                    // Index might already exist
                }
            });
        }

        // Audit Log - add indexes for common filtering patterns
        if (Schema::hasTable('audit_log')) {
            Schema::table('audit_log', function (Blueprint $table) {
                try {
                    $table->index(
                        ['log_date', 'user_id'],
                        'idx_audit_date_user'
                    );
                } catch (\Exception $e) {
                }
            });
        }

        // 7. QUERY OPTIMIZATION TIPS
        // These are applied in the controllers:
        // - Vertical Partitioning: Selecting only necessary columns
        // - Aggregation: Using SUM, COUNT, AVG in queries instead of application code
        // - Caching: Using Redis for frequently accessed data (services list, stats)
        // - Index hints: Utilizing indexes on email, status, scheduled_date, queue_number
        // - De-normalization: Using stat tables for dashboard aggregations
        // - Horizontal Partitioning: Archiving old appointments to separate table
        // - Batch Operations: Using transactions for multiple updates
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_stats');
        Schema::dropIfExists('patient_stats');
        Schema::dropIfExists('login_stats');
        Schema::dropIfExists('appointments_archive');

        // Remove archived_at column if it exists
        if (Schema::hasColumn('appointments', 'archived_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
